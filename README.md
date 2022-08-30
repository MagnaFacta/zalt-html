# zalt-html
Zalt html is a PHP library for easy Html use and creation, with minimal dependencies on external libraries.

The library consist of three sub-packages:

1. **Html** The (main) Zalt-Html code makes it easy to generate HTML output using objects.
2. **Late** Objects that allow setting e.g. HTML attributes like colspan while the number of columns is not yet known.
3. **Snippets** Objects that combine HTML generation with business logic code and limited routing.

## Html

The Html package is meant for the easy creation, extension and changing of HTML objects. 

```php
$div = Html::create('div', ['class' => 'my-div']);
$div->img('hello.png', ['style' => 'float: right;']);
$p = $div->p('This will be <HTML>. ', ['data-xyz' => 'whatever']);
$p->raw('This <b>bold</>.');
echo $div->render();
```

Will output something like:

```html
<div class="my-div"><img src="/images/hello.png" width="20" height="20" style="float: right;"/>
    <p data-xyz="whatever">
        This will be &amp;lt;HTML&amp;gt;. This <b>bold</b>. 
    </p>
</div>
```

The package has some knowledge of HTML, as demonstrated by the automatic adaptation on the image element, but this
is very limited and usually does not get in the way of outputting what you want, as demonstrated by teh data-xyz 
attribute of the P element. All text is automatically escaped, except when the Raw object is used. 

## Late

The Late package is a package for defining code that will be evaluated at a later stage of code execution, usually when 
rendering the actual Html objects for output.


## Snippets
