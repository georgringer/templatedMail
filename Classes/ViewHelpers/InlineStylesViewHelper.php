<?php
declare(strict_types=1);

namespace GeorgRinger\Templatedmail\ViewHelpers;

use Pelago\Emogrifier;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class InlineStylesViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        $this->registerArgument('css', 'string', 'Path to file', true);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        $html = $renderChildrenClosure();
        $css = file_get_contents(GeneralUtility::getFileAbsFileName($arguments['css']));

        $emogrifier = new Emogrifier($html, $css);
        return $emogrifier->emogrify();
    }
}
