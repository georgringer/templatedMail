<?php
declare(strict_types=1);

namespace GeorgRinger\Templatedmail\Mail;


use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Routing\SiteMatcher;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class TemplatedEmail extends MailMessage
{
    public const FORMAT_HTML = 'html';
    public const FORMAT_PLAIN = 'txt';

    /** @var array */
    protected $layoutRootPaths = [];

    /** @var array */
    protected $partialRootPaths = [];

    /** @var array */
    protected $templateRootPaths = [];

    /** @var StandaloneView */
    protected $view;

    /** @var SiteInterface */
    protected $site;

    /** @var string */
    protected $language = '';

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

    /**
     * @param SiteInterface $site
     */
    public function setSite(SiteInterface $site): void
    {
        $this->site = $site;
    }

    /**
     * @param string $language
     * @return TemplatedEmail
     */
    public function setLanguage(string $language): TemplatedEmail
    {
        $this->language = $language;
        return $this;
    }

    public function addContentAsFluidTemplate(string $templateName, array $variables = [], string $format = self::FORMAT_HTML): TemplatedEmail
    {
        $this->init($format);
        $this->view->setTemplate($templateName . '.' . $format);
        $this->view->assignMultiple($variables);

        $this->addContent($format, $this->view->render());
        return $this;
    }

    public function addContentAsFluidTemplateFile(string $templateFile, array $variables = [], string $format = self::FORMAT_HTML): TemplatedEmail
    {
        $this->init($format);
        $this->view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templateFile));
        $this->view->assignMultiple($variables);

        $this->addContent($format, $this->view->render());
        return $this;
    }

    public function addContentAsRaw(string $content, string $format = self::FORMAT_HTML, string $templateName = 'Raw'): TemplatedEmail
    {
        $this->init($format);
        $this->view->setTemplate($this->resolveLanguageSuffix($templateName, $format));
        $this->view->assign('content', $content);

        $this->addContent($format, $this->view->render());
        return $this;
    }

    protected function resolveLanguageSuffix(string $template, string $format): string
    {
        if ($this->language) { // todo add language
            $path = $template . '.' . $format;
        } else {
            $path = $template . '.' . $format;
        }
 
        return $path;
    }

    protected function addContent(string $format, string $content): void
    {
        if ($format === self::FORMAT_HTML) {
            $this->setBody($content, 'text/html');
        } elseif ($format === self::FORMAT_PLAIN) {
            $this->addPart($content, 'text/plain');
        } else {
            throw new \UnexpectedValueException(sprintf('Given format "%s" is unknown', $format), 1552682965);
        }
    }


    protected function init(string $format): void
    {
        $site = $this->site ?: $this->getCurrentSite();
        if ($site) {
            $configuration = $site->getConfiguration();
            if (isset($configuration['templatedEmail'])) {
                $templatePaths = $configuration['templatedEmail']['templateRootPaths'] ?? [];
                if ($templatePaths) {
                    $this->templateRootPaths = $templatePaths;
                }
                $partialPaths = $configuration['templatedEmail']['partialRootPaths'] ?? [];
                if ($partialPaths) {
                    $this->partialRootPaths = $partialPaths;
                }
                $layoutPaths = $configuration['templatedEmail']['layoutRootPaths'] ?? [];
                if ($layoutPaths) {
                    $this->layoutRootPaths = $layoutPaths;
                }
            }
        } else {
            $path = GeneralUtility::getFileAbsFileName('EXT:templatedmail/Resources/Private/');
            $this->templateRootPaths = [$path . 'Templates/'];
            $this->layoutRootPaths = [$path . 'Layouts/'];
            $this->partialRootPaths = [$path . 'Partials/'];
        }

        if (!$this->language) {
            $siteLanguage = $this->getCurrentSiteLanguage();
            if ($siteLanguage) {
                $this->language = $siteLanguage->getTwoLetterIsoCode();
            }
        }
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->setLayoutRootPaths($this->layoutRootPaths);
        $this->view->setTemplateRootPaths($this->templateRootPaths);
        $this->view->setPartialRootPaths($this->partialRootPaths);
        $this->view->setFormat($format);

        $this->view->assignMultiple($this->getDefaultVariables());

        $css = file_get_contents(ExtensionManagementUtility::extPath('templatedmail') . 'Resources/Public/Css/simple.css');
        $this->view->assign('css', $css);
        $this->view->assign('site', $site);
        $this->view->assign('siteLanguage', $siteLanguage);
        $this->view->assign('language', $this->language);
    }

    protected function getCurrentSite(): ?SiteInterface
    {
        if ($GLOBALS['TYPO3_REQUEST'] instanceof ServerRequestInterface) {
            return $GLOBALS['TYPO3_REQUEST']->getAttribute('site', null);
        }
        if (MathUtility::canBeInterpretedAsInteger($GLOBALS['TSFE']->id) && $GLOBALS['TSFE']->id > 0) {
            $matcher = GeneralUtility::makeInstance(SiteMatcher::class);
            try {
                $site = $matcher->matchByPageId((int)$GLOBALS['TSFE']->id);
            } catch (SiteNotFoundException $e) {
                $site = null;
            }
            return $site;
        }
        return null;
    }

    /**
     * Returns the currently configured "site language" if a site is configured (= resolved) in the current request.
     */
    protected function getCurrentSiteLanguage(): ?SiteLanguage
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        return $request
        && $request instanceof ServerRequestInterface
        && $request->getAttribute('language') instanceof SiteLanguage
            ? $request->getAttribute('language')
            : null;
    }

    protected function getDefaultVariables(): array
    {
        return [
            'default' => [
                'sitename' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'],
            ]
        ];
    }
}
