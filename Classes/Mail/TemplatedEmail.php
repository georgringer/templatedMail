<?php

namespace GeorgRinger\Templatedmail\Mail;


use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class TemplatedEmail extends MailMessage
{

    /** @var array */
    protected $layoutRootPaths = [];

    /** @var array */
    protected $partialRootPaths = [];

    /** @var array */
    protected $templateRootPaths = [];

    /** @var string */
    protected $content = '';

    /** @var StandaloneView */
    protected $view;

    protected $templateFormat = 'text/html';

    public const FORMAT_HTML = 'html';
    public const FORMAT_PLAIN = 'txt';


    /**
     * @param array $layoutRootPaths
     */
    public function setLayoutRootPaths(array $layoutRootPaths): void
    {
        $this->layoutRootPaths = $layoutRootPaths;
    }

    /**
     * @param array $partialRootPaths
     */
    public function setPartialRootPaths(array $partialRootPaths): void
    {
        $this->partialRootPaths = $partialRootPaths;
    }

    /**
     * @param array $templateRootPaths
     */
    public function setTemplateRootPaths(array $templateRootPaths): void
    {
        $this->templateRootPaths = $templateRootPaths;
    }

    public function addContentAsFluidTemplate(string $templateName, array $variables = [], string $format = self::FORMAT_HTML)
    {
        $this->init($format);
        $this->view->setTemplate($templateName . '.' . $format);
        $this->view->assignMultiple($variables);

        $this->addContent($format, $this->view->render());
        return $this;
    }

    public function addContentAsFluidTemplateFile(string $templateFile, array $variables = [], string $format = self::FORMAT_HTML)
    {
        $this->init($format);
        $this->view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templateFile));
        $this->view->assignMultiple($variables);

        $this->addContent($format, $this->view->render());
        return $this;
    }

    public function addContentAsRaw(string $content, string $format = self::FORMAT_HTML, string $templateName = 'Raw')
    {
        $this->init($format);
        $this->view->setTemplate($templateName . '.' . $format);
        $this->view->assign('content', $content);

        $this->addContent($format, $this->view->render());
        return $this;
    }

    protected function addContent(string $format, string $content)
    {
        if ($format === self::FORMAT_HTML) {
            $this->setBody($content, 'text/html');
        } else {
            $this->addPart($content, 'text/plain');
        }
    }


    protected function init(string $format)
    {
        $path = GeneralUtility::getFileAbsFileName('EXT:templatedmail/Resources/Private/');
        $this->templateRootPaths = [$path . 'Templates/'];
        $this->layoutRootPaths = [$path . 'Layouts/'];
        $this->partialRootPaths = [$path . 'Partials/'];

        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->setLayoutRootPaths($this->layoutRootPaths);
        $this->view->setTemplateRootPaths($this->templateRootPaths);
        $this->view->setPartialRootPaths($this->partialRootPaths);
        $this->view->setFormat($format);

        $this->view->assignMultiple($this->getDefaultVariables());

        $css = file_get_contents(ExtensionManagementUtility::extPath('templatedmail') . 'Resources/Public/Css/simple.css');
        $this->view->assign('css', $css);


    }

    protected function getDefaultVariables()
    {
        return [
            'default' => [
                'sitename' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'],
            ]
        ];
    }
}
