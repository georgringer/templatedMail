# TYPO3 extension `templatedMail`

This extension is a proof of concept how to improve the templating of mails.

**Current Benefits**

- All mails share the same layout which makes it easier to style mails
- It is faster to create nice mails


## Usage

```
$templatedMail = GeneralUtility::makeInstance(TemplatedEmail::class);
$templatedMail->addTo('dummy@example.org')
    ->addFrom('noreply@fo.com', 'Test')
    ->setSubject('A mail')
    ->addContentAsRaw('Hello' . LF . 'an example', TemplatedEmail::FORMAT_PLAIN)
    ->addContentAsRaw('<h1>Hello</h1> an example', TemplatedEmail::FORMAT_HTML)
    ->send();
```
The example can also be called by CLI with `./web/bin/typo3 mail:template`.

This example will send one mail with the following parts:

**HTML part**

![HTML](Resources/Public/Screenshots/example-html.png)

**Plain text part**

![Plain](Resources/Public/Screenshots/example-txt.png)

## Further examples

### Using A template file

```
$templatedMail = GeneralUtility::makeInstance(TemplatedEmail::class);
$templatedMail->addTo('reciepient@example.org')
    ->addFrom('noreply@fo.com', 'Test')
    ->setSubject('A mail')
    ->addContentAsFluidTemplateFile('EXT:templatedmail/Resources/Private/Templates/Examples/Example.html', ['title' => 'My title'], TemplatedEmail::FORMAT_HTML)
    ->send();
```

### Using A template

```
$templatedMail = GeneralUtility::makeInstance(TemplatedEmail::class);
$templatedMail->addTo('dummy@example.org')
    ->addFrom('noreply@fo.com', 'Test')
    ->setSubject('A mail')
    ->setTemplateRootPaths(['EXT:dummy/Resources/Private/Templates/'])
    ->addContentAsFluidTemplate('Examples/Simple', ['title' => 'My title'], TemplatedEmail::FORMAT_HTML)
    ->addContentAsFluidTemplate('Examples/Simple', ['title' => 'My title'], TemplatedEmail::FORMAT_PLAIN)
    ->send();
```

## Requirements

- TYPO3 8.7 / 9.5 LTS
- GPL License
