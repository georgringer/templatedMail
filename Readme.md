# TYPO3 extension `templatedMail`

This extension is a proof of concept how to improve the templating of mails.
Benefits

- All mails share the same layout which makes it easier to style mails
- It is faster to create nice mails

## Examples

Some example usages

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
    ->addContentAsFluidTemplate('Simple', ['title' => 'My title'], TemplatedEmail::FORMAT_HTML)
    ->addContentAsFluidTemplate('Simple', ['title' => 'My title'], TemplatedEmail::FORMAT_PLAIN)
    ->send();
```

### Using predefined content

```
$templatedMail = GeneralUtility::makeInstance(TemplatedEmail::class);
$templatedMail->addTo('reciepient@example.org')
    ->addFrom('noreply@fo.com', 'Test')
    ->setSubject('A mail')
    ->addContentAsRaw('Some basic text', TemplatedEmail::FORMAT_PLAIN)
    ->addContentAsRaw('<h1>Hello</h1> and more text', TemplatedEmail::FORMAT_HTML)
    ->send();
```
