<?php
declare(strict_types=1);

namespace GeorgRinger\Templatedmail\Mail;

use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Routing\SiteMatcher;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\IpAnonymizationUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class TemplatedEmail extends MailMessage
{
    private const FORMAT_HTML = 'html';
    private const FORMAT_PLAIN = 'txt';

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

    public function __construct($subject = null, $body = null, $contentType = null, $charset = null)
    {
        parent::__construct($subject, $body, $contentType, $charset);

        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $path = GeneralUtility::getFileAbsFileName('EXT:templatedmail/Resources/Private/');
        $this->templateRootPaths = [$path . 'Templates/'];
        $this->layoutRootPaths = [$path . 'Layouts/'];
        $this->partialRootPaths = [$path . 'Partials/'];
    }

    /**
     * @param array $layoutRootPaths
     */
    public function setLayoutRootPaths(array $layoutRootPaths): self
    {
        $this->layoutRootPaths = $layoutRootPaths;
        return $this;
    }

    /**
     * @param array $partialRootPaths
     */
    public function setPartialRootPaths(array $partialRootPaths): self
    {
        $this->partialRootPaths = $partialRootPaths;
        return $this;
    }

    /**
     * @param array $templateRootPaths
     */
    public function setTemplateRootPaths(array $templateRootPaths): self
    {
        $this->templateRootPaths = $templateRootPaths;
        return $this;
    }

    /**
     * @param SiteInterface $site
     */
    public function setSite(SiteInterface $site): self
    {
        $this->site = $site;
        return $this;
    }

    /**
     * @param string $language
     * @return TemplatedEmail
     */
    public function setLanguage(string $language): self
    {
        $this->language = $language;
        return $this;
    }

    public function htmlTemplateName(string $templateName): self
    {
        $format = self::FORMAT_HTML;
        $this->init($format);
        $this->view->setTemplate($templateName . '.' . $format);

        $this->setBody($this->view->render(), 'text/html');
        return $this;
    }

    public function textTemplateName(string $templateName): self
    {
        $format = self::FORMAT_PLAIN;
        $this->init($format);
        $this->view->setTemplate($templateName . '.' . $format);

        $this->addPart($this->view->render(), 'text/plain');
        return $this;
    }

    public function context(array $variables): self
    {
        $this->view->assignMultiple($variables);
        return $this;
    }

    public function htmlTemplateFile(string $templateFile): self
    {
        $this->init($format);
        $this->view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templateFile));

        $this->setBody($this->view->render(), 'text/html');
        return $this;
    }

    public function textTemplateFile(string $templateFile): self
    {
        $this->init($format);
        $this->view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templateFile));

        $this->addPart($this->view->render(), 'text/plain');
        return $this;
    }

    public function htmlContent(string $content, string $templateName = 'Default'): self
    {
        $this->addContent($content, self::FORMAT_HTML, $templateName);
        return $this;
    }

    public function textContent(string $content, string $templateName = 'Default'): self
    {
        $this->addContent($content, self::FORMAT_PLAIN, $templateName);
        return $this;
    }

    private function addContent(string $content, string $format, string $templateName): self
    {
        $this->init($format);
        $this->view->setTemplate($this->resolveLanguageSuffix($templateName, $format));
        $this->view->assign('content', $content);

        if ($format === self::FORMAT_HTML) {
            $this->setBody($this->view->render(), 'text/html');
        } elseif ($format === self::FORMAT_PLAIN) {
            $this->addPart($this->view->render(), 'text/plain');
        }
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

    protected function init(string $format): void
    {
        if (class_exists(Site::class)) {
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
            }
            if (!$this->language) {
                $siteLanguage = $this->getCurrentSiteLanguage();
                if ($siteLanguage) {
                    $this->language = $siteLanguage->getTwoLetterIsoCode();
                }
            }
            $this->view->assign('site', $site);
            $this->view->assign('siteLanguage', $siteLanguage);
        }

        $this->view->setLayoutRootPaths($this->layoutRootPaths);
        $this->view->setTemplateRootPaths($this->templateRootPaths);
        $this->view->setPartialRootPaths($this->partialRootPaths);
        $this->view->setFormat($format);
        $this->view->assign('defaults', $this->getDefaultVariables());

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
            'sitename' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'],
            'clientIp' => IpAnonymizationUtility::anonymizeIp(GeneralUtility::getIndpEnv(GeneralUtility::getIndpEnv('REMOTE_ADDR')))
        ];
    }
}
