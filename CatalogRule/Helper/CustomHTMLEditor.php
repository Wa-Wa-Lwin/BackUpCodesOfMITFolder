<?php

namespace MIT\CatalogRule\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class CustomHTMLEditor extends AbstractHelper
{

    /**
     * get slider image url and href from block content
     * @param string $content
     * @param string $firstStartWords
     * @param string $firstEndWords
     * @param string $secondStartWords
     * @param string $secondEndWords
     * @param string $commonStartBlockWords
     * @return array
     */
    public function retrieveDataFromBlockContent($content, $firstStartWords, $firstEndWords, $secondStartWords, $secondEndWords, $commonStartBlockWords)
    {
        $urlArr = [];
        $firstIdx = strpos($content, $commonStartBlockWords . '"');
        $lastIdx = $this->getLastIdxToRemove('<div class="item ' . $commonStartBlockWords . '"', '</div>', $content, 0, 2);

        $actLen = $lastIdx - $firstIdx;
        $updatedBlock = substr($content, $firstIdx, $actLen);
        if ($updatedBlock) {
            // $updatedData = substr($content, $firstIdx + strlen($commonStartBlockWords));
            $urlArr = $this->getCustomData($updatedBlock, $firstStartWords, $firstEndWords, $secondStartWords, $secondEndWords, $urlArr);
        }
        return $urlArr;
    }

    /**
     * check slider already exists or not
     * @param int $ruleId
     * @param string $content
     * @return bool
     */
    public function checkSlideExist($content, $data)
    {
        return strpos($content, $data) ? true : false;
    }

    /**
     * remove specific slider block from block content
     * @param string $content
     * @param string $startDivWords
     * @param string $endDivWords
     * @param int $endIdx
     * @param int $step
     * @return string
     */
    public function removeSliderByPromoCodeId($content, $startDivWords, $endDivWords, $endIdx = 0, $step = 3)
    {
        $lastIdx = $this->getLastIdxToRemove($startDivWords, $endDivWords, $content, $endIdx, $step);

        $firstIdx = strpos($content, $startDivWords);

        $actLen = $lastIdx - $firstIdx;
        $udata = substr_replace($content, '', $firstIdx, $actLen);
        return $udata;
    }

    /**
     * get last index to remove from block content
     * @param string $startWord
     * @param string $endWord
     * @param string $data
     * @param int $endIdx
     * @param int $step
     * @return int
     */
    public function getLastIdxToRemove($startWord, $endWord, $data, $endIdx = 0, $step = 3)
    {
        if ($step > 0) {
            if ($endIdx == 0) {
                $firstIdx = strpos($data, $startWord);
                if ($firstIdx) {
                    $updatedData = substr($data, $firstIdx);
                    $endIdx += $firstIdx + strpos($updatedData, $endWord) + strlen($endWord);
                    return $this->getLastIdxToRemove($startWord, $endWord, $data, $endIdx, $step - 1);
                }
            } else {
                $updatedData = substr($data, $endIdx);
                $endIdx +=  strpos($updatedData, $endWord) + strlen($endWord);
                return $this->getLastIdxToRemove($startWord, $endWord, $data, $endIdx, $step - 1);
            }
        }
        return $endIdx;
    }

    /**
     * find and retrieve value using start and end word
     * @param string $data
     * @param string $startWord
     * @param string $endWord
     * @param string $secondStartWord
     * @param string $secondEndWord
     * @param array $imageArr
     * @return array $imageArr
     */
    public function getCustomData($data, $startWord, $endWord, $secondStartWord, $secondEndWord, $imageArr)
    {
        $firstIdx = strpos($data, $startWord);
        if ($firstIdx) {
            $updatedData = substr($data, $firstIdx + strlen($startWord), -1);
            $lastIdx = strpos($updatedData, $endWord);
            if ($lastIdx) {
                $imageArr[] = substr($updatedData, 0, $lastIdx);

                // $urlFirstIdx = strpos($updatedData, $secondStartWord);
                // if ($urlFirstIdx) {
                //     $updatedData = substr($updatedData, $urlFirstIdx + strlen($secondStartWord), -1);
                //     $urlLastIdx = strpos($updatedData, $secondEndWord);
                //     if ($urlLastIdx) {
                //         $imageArr[] = substr($updatedData, 0, $urlLastIdx);
                //     }
		// }
		$count = 1;
                while($count <= 3 && $secondStartWord && $secondEndWord) {
                    $urlFirstIdx = strpos($updatedData, $secondStartWord);
                    if ($urlFirstIdx) {
                        $updatedData = substr($updatedData, $urlFirstIdx + strlen($secondStartWord), -1);
                        $urlLastIdx = strpos($updatedData, $secondEndWord);
                        if ($urlLastIdx) {
                            $imageArr[] = substr($updatedData, 0, $urlLastIdx);
                        }
                    }
                   $count++; 
                }
            } else {
                return $imageArr;
            }
        } else {
            return $imageArr;
        }
        return $this->getCustomData($updatedData, $startWord, $endWord, $secondStartWord, $secondEndWord, $imageArr);
    }

    /**
     * remove empty new lines and format data
     * @param string $content
     * @return string
     */
    public function formatContent($content)
    {
        $formattedData = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $content);
        $formattedData = htmlentities($formattedData, ENT_NOQUOTES);
        $formattedData = $this->str_replace_first('&lt;', '<', $formattedData);
        $formattedData = $this->str_replace_first('&gt;', '>', $formattedData);
        $formattedData = $this->str_replace_last('&lt;', '<', $formattedData);
        $formattedData = $this->str_replace_last('&gt;', '>', $formattedData);
        return $formattedData;
    }

    /**
     * convert special HTML entities to character
     * @param string $content
     * @return string
     */
    public function convertHTMLEntities($content)
    {
        return htmlspecialchars_decode($content);
    }

    /**
     * replace only first occurrence
     * @param string $search
     * @param string $replace
     * @param string $content
     * @return string
     */
    private function str_replace_first($search, $replace, $content)
    {
        $pos = strpos($content, $search);
        $newstring = '';
        if ($pos !== false) {
            $newstring = substr_replace($content, $replace, $pos, strlen($search));
        }
        return $newstring;
    }

    /**
     * replace only last occurrence
     * @param string $search
     * @param string $replace
     * @param string $content
     * @return string
     */
    private function str_replace_last($search, $replace, $content)
    {
        $newstring = '';
        if (($pos = strrpos($content, $search)) !== false) {
            $search_length = strlen($search);
            $newstring = substr_replace($content, $replace, $pos, $search_length);
        }
        return $newstring;
    }
}
