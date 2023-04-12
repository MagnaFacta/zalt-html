# zalt-html
Zalt html is a PHP library for easy Html use and creation, with minimal dependencies on external libraries.

The library consist of three sub-packages:

1. **Html** The (main) Zalt-Html code makes it easy to generate HTML output using objects.
2. **Snippets** Objects that combine HTML generation with business logic code and limited routing.
3. **SnippetsLoader** Objects that load the snippets and take care of dependency injection, etc...
4. **SnippetsHandler** MiddelWare RequestHandlerInterface classes that can be used to load and run SnippetActions
5. **SnippetsActions** Combinations of Snippet (classes) and options for Snippets to set  

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
is very limited and usually does not get in the way of outputting what you want, as demonstrated by the data-xyz 
attribute of the P element. All text is automatically escaped, except when the Raw object is used. 

The philosophical idea is that as a programmer I know HTMl and do not want to learn a complex object interface that 
limits what I can and cannot do. So the basics for using the HtmlElement objects are:
- Use it as an array or object whatever is convenient at that moment.
- Things appended using a text key are an attribute.
- Things appended using a numeric key or no key are content.
- Append the content child tag``<nonExisitingFunction/>``HtmlElement by calling``$htmlElement->nonExisitingFunction()``.

### Late integration

The Html package objects can handle [zalt-late](https://github.com/MagnaFacta/zalt-late) library objects. Late objects
are objects that are evaluated when the Html output is generated, not before. Late objects are not a form of lazy 
programming as the output of a late object can change depending on the moment of evaluation. For example adding a 
``RepeatableInterface`` object a Html object, repeats the output of e.g. a table row for each separate row of data - 
without having to write a loop.  

Zalt html works fine without Late objects, check the Zalt late library if you are interested.


## Snippets

Snippets are objects containing reusable html content with added simple *logic* build in. When generating output, using 
a list of snippets, the system:

1. Checks for HtmlOutput and if it is there, add the HTML to the output.
2. Without HTML check for a ``ResponseInterface`` object and return that if it exists.
3. Without HTML or a Response object, check for a Redirect Route, Return as ``RedirectResponse`` if that exists.
4. Go back to step 1 for the next snippet if any.
5. After the last snippet, combine all the HTML output in a ``HtmlResponse``.

This limited logic allows for a completely responsive application. The snippets used depend (of course) on the route
used by the user.

### Form Snippets

Form snippets illustrate the process very well. After loading a basic form snippet, these steps are executed:

1. Load form data.
2. Load the form object.
3. Check for post, when validated **redirect to another route**.
4. Otherwise, *no post, not validated, no route specified*: show the form.

So either the form is displayed or the user is redirected to some result page.

### Model Snippets

Another important group of snippets use the ```ModelSnippetTrait```. These snippets use ``MetaModelInterface`` objects 
from the [zalt-model](https://github.com/MagnaFacta/zalt-model) library. These objects contain metadata on Model View 
Controller (MVC) datamodels to present a standardized view on that data, like create / edit forms, detail tables and
(searchable) browse tables. 

This output is usually generated by model bridges that generate the output for the (selected) fields in the model. 
Using a metamodel ensures that the labels and descriptions used for fields are the same in all views of the data. 
Zalt html works fine without the models, but check the Zalt model library if you are interested.    


## SnippetsLoader

Snippets use objects and scalar options to determine the output. Some of these objects depend on the request object 
generated by middleware in a Psr standard application (e.g. the request object itself). So a standard application level
ServiceManager usually does not contain all the objects needed by snippets. 

As a solution we use is a specialized **SnippetLoaderInterface** object with an ``addConstructorVariable()`` function 
that allows the addition of extra objects in addition to the existing ServiceManager classes. The SnippetLoader uses a 
constructor dependency resolver that looks at the object constructor to determine the required object for initiation.

To pass scalars values (e.g. ``$createData = true``) to a snippet we use a SnippetOption containing an array of 
values. This functionality was created for scalars, but this method *can also be used to pass extra objects* to the 
snippet! SnippetOptions are usually passed as the first object in the snippet constructor. The object is not stored 
in the snippet but is evaluated using the ``setSnippetOptions()`` function. 

### The SnippetResponderInterface

If all that is required is to output some Snippets (with certain options) somewhere in your code, the easiest route
is to use a ``SnippetResponderInterface`` object in your ``RequestHandlerInterface``. 

The ``MezzioLaminasSnippetResponder`` is a useable example implementation. Usually this class in extended to handle 
the output in a manner required by the application, e.g. to add a fixed layout to a page.


## SnippetsHandler

The SnippetsHandler implements the ``RequestHandlerInterface`` and can be used to combine several route endpoints
in a single handler class. The endpoints are stored in a static ``$actions`` array containing endpoint names as keys 
and ``SnippetActionInterface`` object names as values.

The handler selects the current action and initiates the ``SnippetActionInterface`` object. This action object then 
generates the list of snippets to use and a ``SnippetOptions`` object. These snippets are then run through the 
Snippet Responder and the output is the result of the call to the application.

To change the route endpoints of a SnippetHandler, just create a subclass of SnippetHandler and override the ``$actions``
array with your own SnippetActions. If you want to adjust the SnippetOption values or the list of Snippets in the 
SnippetAction, overrule the ``prepareAction(SnippetActionInterface $action)`` function of the handler and set
the appropriate values. Using interfaces and classnames in ``instanceof`` statements you can use code completion
to set the options to the specific value for the current situation.

### Generating routes from a handler

SnippetHandlers also contain a static ``$parameters`` array containing parameter names as keys and as values regular 
expressions for the parameter values. In combination with interfaces implemented by the diverse action classes 
this should contain enough information to be able to generate routing table entries using just the class name i.e.
without instantiating the class.

The action interfaces are described in more detail in the next section.


## SnippetsActions