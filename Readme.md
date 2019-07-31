# TYPO3 extension `templatedMail`

This extension is a proof of concept how to improve the templating of mails.
The plans are to ship this code with TYPO3 10 and provide the extension for 9x.

**Benefits**

- **All** mails share the same layout which makes it easier to style mails
- It is faster to create nice mails

## Requirements

- TYPO3 10
- PHP 7.2

## Usage

```php
$templatedEmail = GeneralUtility::makeInstance(TemplatedEmail::class);
$templatedEmail
    ->to('dummy@example.org')
    ->from(new NamedAddress('noreply@example.org', 'TYPO3'))
    ->subject('A mail')
    ->htmlContent('<h1>Hello</h1> an example')
    ->textContent('Hello' . LF . 'an example')
    ->send();
```

This example will send one mail with the following parts:

|                       HTML part                        |                    Plain text part                     |
|:------------------------------------------------------:|:------------------------------------------------------:|
| ![HTML](Resources/Public/Screenshots/example-html.png) | ![Plain](Resources/Public/Screenshots/example-txt.png) |


## Further examples

The examples can also be called by CLI with `./web/bin/typo3 mail:template`.

### Using a single template file

```php
$templatedEmail = GeneralUtility::makeInstance(TemplatedEmail::class);
$templatedEmail
    ->to('dummy@example.org')
    ->from(new NamedAddress('noreply@example.org', 'TYPO3'))
    ->subject('A mail')
    ->context(['title' => 'My title'])
    ->htmlTemplateFile('EXT:templatedmail/Resources/Private/Templates/Examples/Example.html')
    ->send();
```

### Using custom template paths

```php
$templatedEmail = GeneralUtility::makeInstance(TemplatedEmail::class);
$templatedEmail
    ->to('dummy@example.org')
    ->from(new NamedAddress('noreply@example.org', 'TYPO3'))
    ->subject('A mail')
    ->setTemplateRootPaths(['EXT:dummy/Resources/Private/Templates/'])
    ->setLayoutRootPaths(['EXT:dummy/Resources/Private/Layouts/'])
    ->context(['title' => 'My title'])
    ->htmlTemplateName('Examples/Simple')
    ->textTemplateName('Examples/Simple')
    ->send();
```

### Providing custom translations

```php
$templatedEmail = GeneralUtility::makeInstance(TemplatedEmail::class);
$templatedEmail
    ->to('dummy@example.org')
    ->from(new NamedAddress('noreply@example.org', 'TYPO3'))
    ->subject('A mail')
    ->setLanguage('de')
    ->context(['title' => 'My title'])
    ->htmlTemplateFile('EXT:templatedmail/Resources/Private/Templates/Examples/Example.html')
    ->send();
```

```html
<f:section name="content">
	<h1>{f:translate(languageKey:defaults.language,key:'LLL:EXT:templatedmail/Resources/Private/Language/dummy.xml:good_morning')}, {title}</h1>
</f:section>
```

## Configuration

The paths are configured in the site configuration

```yaml
templatedMail:
  templateRootPath: EXT:templatedmail/Resources/Private/Templates/
  partialRootPath: EXT:templatedmail/Resources/Private/Partials/
  layoutRootPath: EXT:templatedmail/Resources/Private/Layouts/
```

If a mail is sent via CLI, the used site can be set with `$templatedEmail->setSite($site);`

