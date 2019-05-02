## Description
This package includes a standalone form class and a builder for forms based on Eloquent models.

## Requirements
### General
- `PHP 7.0` or higher  
- `Laravel 5.7` or higher (lower versions are not tested but may work)  
- `holonaut/holo-helper` (will be installed automatically if you install this package via composer)

### View
The blade component I provide is basically tied to using `Google Chrome`.  
You can adjust it or write your own component of course. 

### When using the form builder for Eloquent models:
- your model must use the `mysql` driver  
- your model must be an `Eloquent` model
- the SQL user which reads your model tables
also needs read access to the `information_schema` database

## Examples
### HoloForm
```php
$myForm = new HoloForm('myFormId', 'POST');
$myForm
    ->addInput('firstname', 'text', null, 'required')
    ->addInput('lastname', 'text', null, 'required')
    ->addInput('salutation', 'select', '', 'required', '', ['', 'mr', 'ms'])
    ->addInput('subscribe', 'checkbox', 'Subscribe to newsletter', 'checked')
    ->addInput('submit', 'submit')
;

if($request->has('myFormId')) {
    // get all the form data that has been sent
    $postData = $request->post('myFormId');
    
    // refill the form with the submitted data
    $myForm->updateValues($postData);
    
    // execute logic based on the received data
}

return view('myView', ['myForm' => $myForm]);
```

I provide my own blade component in `/src/resources/components/form.blade.php`.
You can copy it into your resources folder if you want.

The usage inside your view file (let's say myView.blade.php) could look like this:

```php
<div style='width:280px;'>
    @include('components.form', ['form' => $myForm])
</div>
```

### HoloFormBuilder
So far we achieved, that we don't need to update our HTML at all
when we change the form definition in PHP. New fields will be added automatically.

But let's take it one step further and let's get rid of the PHP adjustments too:
generate the form directly from a model (i.e. its table).

```php
// Create an empty form
$personForm = new HoloForm('personForm', 'POST', action('PersonController@create'));

// magic happens here (explanation below)
$personForm = HoloFormBuilder::fieldsFromModel($personForm, new Person);

// add a submit button (isn't done automatically because sometimes I don't want it)
$personForm->addInput('submit', 'submit');
```

These three lines will generate a complete form based on your `Person` model.
If it has 100 columns in a table, the form will have 100 fields to fill.

#### Automatic input types:  
column type contains VARCHAR -> text  
column type contains int -> number  
column name contains password or token -> password  

You can find and extend these settings in HoloFormBuilder::htmlType()

#### Automatic required fields and default values
If the column is set to `not null`, the form field will be marked as `required`  
If the column has a `default` value, the form field will have this value preselected  
If the column has a `comment`, it will be added to the field's title (basic tooltip)

#### Foreign key detection
Now the best part: Let's say `Person` has a field `city`, and in SQL it is
defined as a foreign key to your `cities` table.

The form builder will make the city field a required `select` and pre-fill it with all
the (primary key) values found in the cities table.

### Summary
If you use the HoloForm, HoloFormBuilder and the provided view component,
you can generate a complete form for a model with foreign key detection, in 3-4 lines of code.
You can basically adjust your tables in SQL, and everything else will update automatically.

### Caveats
This is a very basic version yet.
It misses many features and edge-cases and has rather specific requirements.  
Chances are, this packages does not enough, too much or simply different things than you want.

For me, it works amazingly so far, so I still wanted to share it.