<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Components\Log\Parser;

use Bcremer\LineReader\LineReader;

/**
 * @category  Shopware
 * @package   Shopware\Components\Log\Parser
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class FileReader
{
    /**
     * @var LineFormatParser $lineParser
     */
    private $lineParser;

    /**
     * @var string $logsPath
     */
    private $logsPath;

    /**
     * @var string $environment
     */
    private $environment;

    /**
     * @param LineFormatParser $lineParser
     * @param string $logsPath
     * @param string $environment
     */
    public function __construct(LineFormatParser $lineParser, $logsPath, $environment)
    {
        $this->lineParser = $lineParser;
        $this->logsPath = $logsPath;
        $this->environment = $environment;
    }

    /**
     * @param string $logType
     * @param int $offset
     * @param int $limit
     * @param boolean $sortAscending
     * @return array
     */
    public function readEntries($logType, $offset = 0, $limit = -1, $sortAscending = false)
    {
        // Parse log files
        $logFiles = $this->findLogFiles($logType, $sortAscending);

        $lines = new \AppendIterator();
        $linecount = 0;

        foreach ($logFiles as $filePath) {
            $linecount += $this->getNumberOfLines($filePath);

            if (!$sortAscending) {
                $lineGenerator = LineReader::readLinesBackwards($filePath);
            } else {
                $lineGenerator = LineReader::readLines($filePath);
            }
            $lines->append($lineGenerator);
        }

        $entries = [];
        foreach (new \LimitIterator($lines, $offset, $limit) as $lineNumber => $line) {
            // Parse the current line
            $logEntry = $this->lineParser->parseLine($line);
            if (!array_key_exists('id', $logEntry)) {
                $logEntry['id'] = sha1($line);
            }
            $entries[] = $logEntry;
        }

        return [
            'data' => $entries,
            'total' => $linecount,
        ];
    }

    /**
     * @param string $logType
     * @param boolean $sortAscending
     * @return string[]
     */
    private function findLogFiles($logType, $sortAscending)
    {
        // Find log files matching the path, environment and type
        $pattern = '/'.preg_quote($logType, '/').'_'.preg_quote($this->environment, '/').'-.*\.log/';
        $files = scandir($this->logsPath, $sortAscending ? SCANDIR_SORT_ASCENDING : SCANDIR_SORT_DESCENDING);
        $logFiles = array_filter($files, function ($fileName) use ($pattern) {
            return preg_match($pattern, $fileName) === 1;
        });
        $logFiles = array_map(function ($fileName) {
            return $this->logsPath . '/' . $fileName;
        }, $logFiles);

        return $logFiles;
    }

    /**
     * Return the number of lines for the given file.
     * This is faster than iterating fgets.
     * @param string $filePath
     * @return int
     */
    private function getNumberOfLines($filePath)
    {
        $file = new \SplFileObject($filePath, 'r');
        $file->seek(PHP_INT_MAX);

        return $file->key();
    }
}
