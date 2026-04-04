# Validation and errors

## Validation (LeanValidator)

`composer.json` includes **`wscore/leanvalidator`**.  
`AbstractController` exposes **`setValidator()`** and **`validate()`** assuming **`ValidatorInterface`** / **`ValidatorResultInterface`**.

Sample: `appDemo/Application/Forms/SampleLeanValidator.php` and `FormController::onPost()`.

Typical flow:

1. `setValidator($validator)` in the controller constructor.  
2. On POST, call **`validate()`**.  
3. On **`failed()`**, re-render the form or **`redirect()->toRoute(...)`**.  
4. **`getView()`** applies **`setInputs`** from the result; Twig shows errors.

## HTTP errors (Slim)

`getApp()` registers **`addErrorMiddleware`**; HTML errors use **`SimpleErrorHandler`** (`core/Handlers/SimpleErrorHandler.php`).

When **`display_errors`** in `settings.ini` is enabled, detailed errors are shown.

## Fatal errors before bootstrap (`error.php`)

Failures before autoload or Slim are handled by **`ShutdownHandler`** (HTML / log). Tune `setDisplayErrorDetails` for development.

## Sample error controller

`appDemo/Application/Controller/ErrorController.php` demonstrates intentional exceptions and HTTP errors.

## Notes

- Domain validation does **not** have to use LeanValidator; inject your own service and skip `AbstractController::validate()`.  
- To redisplay forms, combine **`withInputs()`** and **`view()`**, or **`messages()->addError()`** for user-facing messages.
