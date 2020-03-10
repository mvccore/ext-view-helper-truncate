<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flídr (https://github.com/mvccore/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/5.0.0/LICENCE.md
 */

namespace MvcCore\Ext\Views\Helpers;

/**
 * Responsibility - truncate plain text or text with html tags to max. chars.
 * - No html tags are truncated, only text content in html code is truncated.
 * - Possibility to setup custom chars for three dots,
 *   html entity `&hellip;` by default, for plain text `...` by default.
 * - Possibility to set default truncating method, if third param to define is not set.
 * @method \MvcCore\Ext\Views\Helpers\TruncateHelper GetInstance()
 */
class TruncateHelper extends \MvcCore\Ext\Views\Helpers\AbstractHelper
{
	/**
	 * MvcCore Extension - View Helper - Assets - version:
	 * Comparison by PHP function version_compare();
	 * @see http://php.net/manual/en/function.version-compare.php
	 */
	const VERSION = '5.0.0-alpha';

	/**
	 * If this static property is set - helper is possible
	 * to configure as singleton before it's used for first time.
	 * Example:
	 *	`\MvcCore\Ext\View\Helpers\Truncate::GetInstance()
	 *		->SetThreeDotsText('&hellip;', TRUE)
	 *		->SetThreeDotsText('...', FALSE)
	 *		->SetAlwaysHtmlMode();`
	 * @var \MvcCore\Ext\Views\Helpers\TruncateHelper
	 */
	protected static $instance;

	/**
	 * Custom substrings to set three dost after truncated text.
	 * If not set by `SetThreeDotsText()` method, there is used
	 * for truncated html `&hellip;` by default and for text `...` by default.
	 * @var string[]|NULL[]
	 */
	protected $threeDotsTexts = [NULL, NULL];

	/**
	 * If `TRUE`, texts will be truncated in html mode,
	 * if there is not third force param `$isHtml` in `Truncate()` method,
	 * if `FALSE` text will be truncated in text mode with the same option.
	 * @var bool|NULL
	 */
	protected $alwaysHtmlMode = NULL;

	/**
	 * Set three dots custom chars - `...` or `&hellip;`,
	 * for html mode or different for text mode.
	 * There is used by default for html mode `&hellip;` and
	 * for text mode `...`.
	 * @param string $threeDotsText
	 * @param bool   $forHtmlText
	 * @return \MvcCore\Ext\Views\Helpers\TruncateHelper
	 */
	public function SetThreeDotsText ($threeDotsText = '&hellip;', $forHtmlText = TRUE) {
		$this->threeDotsTexts[$forHtmlText ? 1 : 0] = $threeDotsText;
		return $this;
	}

	/**
	 * Setup helper to use always html mode truncating
	 * if there is not set third param to use force html or text mode.
	 * @param bool $alwaysHtmlMode
	 * @return \MvcCore\Ext\Views\Helpers\TruncateHelper
	 */
	public function SetAlwaysHtmlMode ($alwaysHtmlMode = TRUE) {
		$this->alwaysHtmlMode = $alwaysHtmlMode;
		return $this;
	}

	/**
	 * Truncate text with any HTML tags inside, keep all tags and truncate text content only
	 * or truncate simple text without any HTML tags.
	 * Clean all possible punctuation and brackets before three dots: `',.:;?!+"\'-–()[]{}<>=$§ '`.
	 * @param string $text		Text content or html content to truncate.
	 * @param int $maxChars		Max text chars or max. html content chars.
	 * @param bool|NULL $isHtml	If `TRUE`, first param will be force truncated in html mode,
	 *							If `FALSE`, first param will be force truncated in text mode,
	 *							If `NULL`, there is used possibly configured property
	 *							`\MvcCore\Ext\View\Helpers\::$alwaysHtmlMode` and if not configured,
	 *							there is automatically detected if first param contains any html tag(s).
	 * @return string
	 */
	public function Truncate ($text, $maxChars = 200, $isHtml = NULL) {
		if ($isHtml === NULL) {
			$isHtml = $this->alwaysHtmlMode;
			if ($isHtml === NULL) {
				preg_match("#\<(.+)\>#", $text, $m);
				$isHtml = count($m) > 0;
			}
		}
		if ($isHtml) {
			return $this->truncateHtml($text, $maxChars);
		} else {
			return $this->truncateText($text, $maxChars);
		}
	}

	/**
	 * Truncate text with any HTML tags inside, keep all tags and truncate text content only.
	 * Clean all possible punctuation and brackets before three dots: `',.:;?!+"\'-–()[]{}<>=$§ '`.
	 * @param string $text
	 * @param int $maxChars
	 * @return string
	 */
	protected function truncateHtml (& $text, $maxChars) {
		$texts = [];
		$index = 0;
		$charsCount = 0;
		// explode all html content to array with text contents and html tags
		while (TRUE) {
			$openTagPos = mb_strpos($text, '<', $index);
			if ($openTagPos === FALSE) {
				$subText = mb_substr($text, $index);
				$subText = preg_replace("#\s+#", ' ', str_replace('&nbsp;', ' ', $subText));
				$texts[] = [TRUE, $subText, $charsCount, mb_strlen($subText)];
				break;
			}
			$closeTagPos = mb_strpos($text, '>', $openTagPos + 1);
			if ($closeTagPos === FALSE) {
				$subText = mb_substr($text, $index);
				$subText = preg_replace("#\s+#", ' ', str_replace('&nbsp;', ' ', $subText));
				$texts[] = [TRUE, $subText, $charsCount, mb_strlen($subText)];
				break;
			}
			$subText = mb_substr($text, $index, $openTagPos - $index);
			$subText = preg_replace("#\s+#", ' ', str_replace('&nbsp;', ' ', $subText));
			$subTag = mb_substr($text, $openTagPos, $closeTagPos + 1 - $openTagPos);
			$subTextLength = mb_strlen($subText);
			$subTagLength = mb_strlen($subTag);
			if ($subTextLength > 0)
				$texts[] = [TRUE, $subText, $charsCount, $subTextLength];
			if ($subTagLength > 0)
				$texts[] = [FALSE, $subTag, 0, $subTagLength];
			$charsCount += $subTextLength;
			if ($charsCount >= $maxChars) break;
			$index = $closeTagPos + 1;
		}
		// if there are more chars in text content:
		if ($charsCount > $maxChars) {
			$threeDotsText = $this->threeDotsTexts[1];
			if ($threeDotsText === NULL) $threeDotsText = '&hellip;';
			// try to put three dots from the end into text content where necessary
			for ($i = count($texts) - 1; $i > -1; $i -= 1) {
				list($type, $subText, $charsCount, $subTextLength) = $texts[$i];
				if (!$type) continue;
				$maxCharsLocal = $maxChars - $charsCount;
				$subText = mb_substr($subText, 0, $maxCharsLocal);
				$lastSpacePos = mb_strrpos($subText, ' ');
				if ($lastSpacePos !== FALSE) {
					$subText = rtrim($subText, ',.:;?!+"\'-–()[]{}<>=$§ ');
					if (mb_strlen($subText) === 0) {
						unset($texts[$i]);
					} else {
						$texts[$i][1] = mb_substr($subText, 0, $lastSpacePos) . $threeDotsText;
						break;
					}
				} else {
					unset($texts[$i]);
				}
			}
			// implode truncated text content and all tags (not truncated) back together
			$result = '';
			foreach ($texts as $textRecord) $result .= $textRecord[1];
			return $result;
		} else {
			return $text;
		}
	}

	/**
	 * Truncate simple text without any HTML tags.
	 * Clean all possible punctuation and brackets before three dots: `',.:;?!+"\'-–()[]{}<>=$§ '`.
	 * @param string $text
	 * @param int $maxChars
	 * @return void
	 */
	protected function truncateText (& $text, $maxChars) {
		if (mb_strlen($text) > $maxChars) {
			$text = preg_replace("#\s+#", ' ', $text);
			$text = mb_substr($text, 0, $maxChars);
			$lastSpacePos = mb_strrpos($text, ' ');
			if ($lastSpacePos !== FALSE) {
				$threeDotsText = $this->threeDotsTexts[0];
				if ($threeDotsText === NULL) $threeDotsText = '...';
				$text = rtrim($text, ',.:;?!+"\'-–()[]{}<>=$§ ');
				$text = mb_substr($text, 0, $lastSpacePos) . $threeDotsText;
			}
		}
	}
}
