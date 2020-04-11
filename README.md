# View

[documentation]: https://ilosey14.github.io/docs/php/view

*A view class for your MVC.*

[Documentation][documentation]

Automating your view model is essential for modern applications.
This library is a great resource for building dynamic sites with a maintainable code base.
Separate your index logic and page content while showcasing rich frontend components with simple implementation.

---

## Why

This was ultimately made as an exercise.

Should I use this?

Probably not.
It *is* vanilla PHP, which could be desirable.
Unless it's exactly what you need, some small library won't compare to the usability, functionality, or maintainability of something like a Symfony or Laravel-based project.
However, as mentioned above, I believe this library is a great resource MVC site design.

---

## Examples

The examples below use the following file structure:
- `public`
  - `some-path`
    - `index.php`
    - `content.html|php`

### Basic Usage

In our `index` file, we create a new view instance for our page.
Then render the page to the client.

```php
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/view.php';
$view = new View('Page Title', __DIR__);

$view->render();
```

The page's body contents in the `content` file gets included by the view class automatically.

### Using Variables

Now, before rendering, we'll query some users from our database.

```php
// set anything to the view instance
$view->users = $results_from_database;
```

Then build the page contents.
All view variables are exposed to the model with a leading underscore.

```html
<div>
    <?php
    if (count($_users)):
        foreach ($_users as $user):
    ?>
    <div class="user">
        <img src="<?=$user->thumbnail_url?>">
        <p><?="$user->first_name $user->last_name"?></p>
    </div>
    <?php
        endforeach;
    else:
    ?>
    <p>No users found.</p>
    <?php endif; ?>
</div>
```

### Flushing Headers and Content

For instances where network optimization is a high priority,
it is necessary to flush any immediate content to the client as soon as possible.
Static content like the page header can be sent first before any lengthy computation takes place.

```php
// get the page to the client ASAP
$view->renderPageHeader();

// here we would do an expensive db query
sleep(3);

// now that we have all our data,
// we'll set it to the view instance
// to be accessible to our page template
$view->foo = 'bar';

// render will pick back up and
// finish rendering the page content
$view->render();
```

*Note*: setting response headers as well as any info needed before rendering the page header should be done before making that call to the view.

---

## Implementation

In each index location where this view model is used, a `content` file must also be present containing the page's body content.
Additionally, a standard set of optional resources may be included in the index directory.
These are given as a recommended, modular approach to separate your code into managable, working pieces.

Here is the full page directory structure:

| File                 |          | Description                                                                             |
| -------------------- | -------- | --------------------------------------------------------------------------------------- |
| `index.php`          |          | Page logic - `View` instance lives here.                                                |
| `content.php|html`   |          | HTML body content with given MVC scope.                                                 |
| `head.php|html`      | Optional | Document head resources                                                                 |
| `libraries.php|html` | Optional | Any front-end libraries and their required content (These come after the page content). |
| `scripts.php|html`   | Optional | Additional inline scripts at the end of the document.                                   |

Now, We need to modify some default templates to get this library customized for you project.

**view.php**'s content structure relies heavily on the `templates/shell.php` template and its logic.
For the most part we are modifying the shell for anything that shows up on every page.

Here are some suggested changes:
- Alter the title to add your site name `<title><?=$this->title?> - My Site Name</title>`
- Add a `<link>` for your main stylesheet to the document head
- Any frontend framework like jQuery, or React (a little weird with PHP but whatever, fullstack rendering it is)
- Include a footer
- Anything else you'd like...

Then, create your templates and/or components (see below) in the `templates` directory.

### Advanced

*Flusing custom content with headers on a page-by-page basis.*

In this senario, we would like to provide additional, immediate content to the client on a certain page.
For this we need to reference an additional index resource before flushing the output buffer.
Given a file `static_banner.html` in the index directory, the template shell should be modified to the following.

```php
...
    // this adds your additional content
    $this->requireResource('static_banner');
...
    // ^ some time before pausing the rendering pipeline
    if ($this->sendingPageHeader) return;
```

Which works in conjunction with `$view->renderPageHeader()` in the `index.php` logic.

---

## Components

*Create template components for reusable page content.*

Add any static or dynamic component to the `templates/components` directory to include them in your templates and `content` files.

Some use cases:
- Menu
- Image carosel
- Item showcase
- Payment form
- Message or dialog box (like my [messagebox.js](https://www.github.com/ilosey14/messagebox)?)
  - Create commonly used content components
  - Also, this was a shameless plug
- And anything else you need...

For example, given the the file structure:
- `public`
  - `some-path`
    - `index.php`
    - `content.php`
- `templates`
  - `components`
    - `menu.html`
    - `item-showcase.php`

we can create the following.

```html
// index.php
$view->item = $item_info_db_result;

// content.php
<div id="content">
    <div class="column">
        <?php $this->includeComponent('menu') ?>
    </div>
    <div class="column">
        <?php $this->includeComponent('item-showcase', $_item) ?>
    </div>
</div>
```

---

## Get Started

Clone this repo to your existing project root.

`git clone https://www.github.com/ilosey14/view.git`

Fork the repo to make lasting changes for your workflow!

Don't forget the [documentation][documentation].