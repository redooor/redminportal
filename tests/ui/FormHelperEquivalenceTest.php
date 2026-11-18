<?php namespace Redooor\Redminportal\Test;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use DOMXPath;
use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\DataProvider;

class FormHelperEquivalenceTest extends RedminTestCase
{
    /**
     * A controller action that is registered by the package routes, so Form::open() can resolve it.
     */
    private const FORM_ACTION = '\Redooor\Redminportal\App\Http\Controllers\CouponController@postStore';

    /**
     * Attributes that mean "present" whether they are written bare or as name="name".
     */
    private const BOOLEAN_ATTRIBUTES = [
        'allowfullscreen', 'async', 'autofocus', 'autoplay', 'checked', 'controls',
        'default', 'defer', 'disabled', 'formnovalidate', 'hidden', 'inert', 'ismap',
        'itemscope', 'loop', 'multiple', 'muted', 'nomodule', 'novalidate', 'open',
        'playsinline', 'readonly', 'required', 'reversed', 'selected',
    ];

    private const TEXT_SNIPPET = <<<'BLADE'
<input type="text" name="n" id="n" value="{{ old('n', $v) }}" class="form-control" required>
BLADE;

    private const EMAIL_SNIPPET = <<<'BLADE'
<input type="email" name="n" id="n" value="{{ old('n', $v) }}" class="form-control" required>
BLADE;

    private const NUMBER_SNIPPET = <<<'BLADE'
<input type="number" name="n" id="n" value="{{ old('n', $v) }}" class="form-control" min="0">
BLADE;

    /**
     * Form::password() emits an empty value="" that the snippet leaves out (an intentional difference).
     */
    private const PASSWORD_SNIPPET = <<<'BLADE'
<input type="password" name="n" id="n" class="form-control">
BLADE;

    private const TEXTAREA_SNIPPET = <<<'BLADE'
<textarea name="n" id="n" class="form-control" rows="3">{{ old('n', $v) }}</textarea>
BLADE;

    private const HIDDEN_SNIPPET = <<<'BLADE'
<input type="hidden" name="n" id="n" value="{{ old('n', $v) }}">
BLADE;

    private const SUBMIT_SNIPPET = <<<'BLADE'
<input type="submit" value="{{ $text }}" class="btn btn-primary">
BLADE;

    private const LABEL_SNIPPET = <<<'BLADE'
<label for="n">{{ $text }}</label>
BLADE;

    private const CHECKBOX_SNIPPET = <<<'BLADE'
<input type="checkbox" name="n" value="{{ $value }}" id="n" class="form-check-input" @checked(session()->hasOldInput() ? old('n') : $default)>
BLADE;

    /**
     * Form::checkbox('n', null, ...) renders no value attribute.
     */
    private const CHECKBOX_NULL_SNIPPET = <<<'BLADE'
<input type="checkbox" name="n" id="n" class="form-check-input" @checked(session()->hasOldInput() ? old('n') : $default)>
BLADE;

    private const CHECKBOX_ARRAY_SNIPPET = <<<'BLADE'
<input type="checkbox" name="media_checkbox[]" value="{{ $value }}" id="{{ $id }}" @checked(in_array($value, (array) (session()->hasOldInput() ? old('media_checkbox', []) : $defaultArray)))>
BLADE;

    private const SELECT_SNIPPET = <<<'BLADE'
<select name="bundle_id" id="bundle_id" class="form-select">@foreach ($options as $k => $label)<option value="{{ $k }}" @selected((string) $k === (string) old('bundle_id', $selected))>{{ $label }}</option>@endforeach</select>
BLADE;

    private const SELECT_MULTIPLE_SNIPPET = <<<'BLADE'
<select name="bundle_id[]" id="bundle_id" class="form-select" multiple>@foreach ($options as $k => $label)<option value="{{ $k }}" @selected(in_array((string) $k, array_map('strval', (array) old('bundle_id', $selected)), true))>{{ $label }}</option>@endforeach</select>
BLADE;

    /**
     * Laravel 13's @csrf adds autocomplete="off" to the token input, while Form::open() does not.
     * Spell the token input out so the form comparison stays exact; the @csrf difference is pinned below.
     */
    private const OPEN_SNIPPET = <<<'BLADE'
<form method="POST" action="{{ $url }}" enctype="multipart/form-data" role="form"><input type="hidden" name="_token" value="{{ csrf_token() }}"></form>
BLADE;

    private const LINK_SNIPPET = <<<'BLADE'
<a href="{{ URL::to('admin/x') }}" class="btn btn-link btn-sm">{{ $title }}</a>
BLADE;

    private const IMAGE_SNIPPET = <<<'BLADE'
<img src="{{ asset('img/x.png') }}" alt="{{ $alt }}" class="img-fluid">
BLADE;

    public function setUp(): void
    {
        parent::setUp();

        $session = app('session.store');

        if (! $session->isStarted()) {
            $session->start();
        }

        if (! $session->has('_token')) {
            $session->regenerateToken();
        }

        $session->forget('_old_input');

        // old() reads the request's session, which a unit test does not wire up for us.
        app('request')->setLaravelSession($session);
        \Form::setSessionStore($session);
    }

    /**
     * Test (Pass): every plain-Blade snippet renders the same canonical DOM as its Form::/HTML:: helper.
     */
    #[DataProvider('helperCases')]
    public function testPlainBladeRendersTheSameMarkupAsTheHelper(array $case): void
    {
        $this->flashOldInput($case['old']);

        $helper = $this->renderHelper($case);
        $snippet = $this->renderSnippet($case);

        $this->assertSame(
            $this->normalise($helper),
            $this->normalise($snippet),
            "Helper and plain-Blade snippet differ for {$case['kind']}.\n"
            . "Helper: {$helper}\n"
            . "Snippet: {$snippet}"
        );
    }

    /**
     * Test (Difference): Collective treats a null old value as absent and uses the non-null default,
     * while old('n', $default) returns null and Blade renders an empty value.
     */
    public function testNullOldValueFallsBackToTheDefaultInTheHelperOnly(): void
    {
        $this->flashOldInput(['n' => null]);

        $helper = (string) \Form::text('n', 'def', ['class' => 'form-control', 'required', 'id' => 'n']);
        $snippet = Blade::render($this->snippet(self::TEXT_SNIPPET), ['v' => 'def']);

        $this->assertSame(
            '<input class="form-control" required id="n" name="n" type="text" value="def">',
            $helper
        );
        $this->assertSame(
            '<input type="text" name="n" id="n" value="" class="form-control" required>',
            $snippet
        );
    }

    /**
     * Test (Difference): Collective does not double-encode an already-encoded attribute value,
     * while Blade's {{ }} escapes the ampersand of the entity again.
     */
    public function testExistingEntityIsDoubleEncodedByBladeOnly(): void
    {
        $this->flashOldInput(null);

        $helper = (string) \Form::text('n', 'A &amp; B', ['class' => 'form-control', 'id' => 'n']);
        $snippet = Blade::render($this->snippet(self::TEXT_SNIPPET), ['v' => 'A &amp; B']);

        $this->assertSame(
            '<input class="form-control" id="n" name="n" type="text" value="A &amp; B">',
            $helper
        );
        $this->assertSame(
            '<input type="text" name="n" id="n" value="A &amp;amp; B" class="form-control" required>',
            $snippet
        );
    }

    /**
     * Test (Difference): @csrf adds autocomplete="off" to its token input in Laravel 13,
     * while Form::open()'s hidden token input has no autocomplete attribute.
     */
    public function testCsrfDirectiveAddsAnAutocompleteAttribute(): void
    {
        $helper = (string) \Form::open([
            'files' => true,
            'action' => self::FORM_ACTION,
            'role' => 'form',
        ]);
        $snippet = Blade::render(
            '<form method="POST" action="{{ $url }}" enctype="multipart/form-data" role="form">@csrf</form>',
            ['url' => action(self::FORM_ACTION)]
        );

        $this->assertStringNotContainsString('autocomplete="off"', $helper);
        $this->assertStringContainsString('autocomplete="off"', $snippet);
        $this->assertStringContainsString('value="' . csrf_token() . '"', $helper);
        $this->assertStringContainsString('value="' . csrf_token() . '"', $snippet);
    }

    /**
     * Return one row per helper kind and state.
     */
    public static function helperCases(): array
    {
        $cases = [
            'text: no old input, default text' => [
                'kind' => 'text', 'old' => null, 'value' => 'Default title',
            ],
            'text: old input value' => [
                'kind' => 'text', 'old' => ['n' => 'Old title'], 'value' => 'Default title',
            ],
            'text: old input empty string' => [
                'kind' => 'text', 'old' => ['n' => ''], 'value' => 'Default title',
            ],
            'text: old input without the key' => [
                'kind' => 'text', 'old' => ['other' => 'value'], 'value' => 'Default title',
            ],
            'text: default null' => [
                'kind' => 'text', 'old' => null, 'value' => null,
            ],
            'email: default null' => [
                'kind' => 'email', 'old' => null, 'value' => null,
            ],
            'input number: default null' => [
                'kind' => 'number', 'old' => null, 'value' => null,
            ],
            'hidden: default null' => [
                'kind' => 'hidden', 'old' => null, 'value' => null,
            ],
            'text: default zero' => [
                'kind' => 'text', 'old' => null, 'value' => 0,
            ],
            'text: default empty string' => [
                'kind' => 'text', 'old' => null, 'value' => '',
            ],
            'text: HTML special characters' => [
                'kind' => 'text', 'old' => null, 'value' => 'He said "hi" <b> & </b>',
            ],
            'text: old input with HTML special characters' => [
                'kind' => 'text', 'old' => ['n' => 'He said "hi" <b> & </b>'], 'value' => 'Default title',
            ],
            'email: no old input, default text' => [
                'kind' => 'email', 'old' => null, 'value' => 'person@example.com',
            ],
            'email: old input value' => [
                'kind' => 'email', 'old' => ['n' => 'old@example.com'], 'value' => 'person@example.com',
            ],
            'email: old input empty string' => [
                'kind' => 'email', 'old' => ['n' => ''], 'value' => 'person@example.com',
            ],
            'input number: no old input, default zero' => [
                'kind' => 'number', 'old' => null, 'value' => 0,
            ],
            'input number: old input value' => [
                'kind' => 'number', 'old' => ['n' => '42'], 'value' => 0,
            ],
            'input number: default empty string' => [
                'kind' => 'number', 'old' => null, 'value' => '',
            ],
            'password: no old input' => [
                'kind' => 'password', 'old' => null,
            ],
            'password: old input is ignored' => [
                'kind' => 'password', 'old' => ['n' => 'secret'],
            ],
            'textarea: no old input, default text' => [
                'kind' => 'textarea', 'old' => null, 'value' => 'Default content',
            ],
            'textarea: old input value' => [
                'kind' => 'textarea', 'old' => ['n' => 'Old content'], 'value' => 'Default content',
            ],
            'textarea: old input empty string' => [
                'kind' => 'textarea', 'old' => ['n' => ''], 'value' => 'Default content',
            ],
            'textarea: old input without the key' => [
                'kind' => 'textarea', 'old' => ['other' => 'value'], 'value' => 'Default content',
            ],
            'textarea: default zero' => [
                'kind' => 'textarea', 'old' => null, 'value' => 0,
            ],
            'textarea: default empty string' => [
                'kind' => 'textarea', 'old' => null, 'value' => '',
            ],
            'textarea: default null' => [
                'kind' => 'textarea', 'old' => null, 'value' => null,
            ],
            'textarea: HTML special characters and a closing tag' => [
                'kind' => 'textarea', 'old' => null, 'value' => 'Line <b>"x" & y</b> </textarea>',
            ],
            'textarea: whitespace is preserved' => [
                'kind' => 'textarea', 'old' => null, 'value' => "  leading\n  middle  trailing  ",
            ],
            'hidden: no old input, default text' => [
                'kind' => 'hidden', 'old' => null, 'value' => 'Default value',
            ],
            'hidden: old input value' => [
                'kind' => 'hidden', 'old' => ['n' => 'Old value'], 'value' => 'Default value',
            ],
            'hidden: old input empty string' => [
                'kind' => 'hidden', 'old' => ['n' => ''], 'value' => 'Default value',
            ],
            'hidden: default zero' => [
                'kind' => 'hidden', 'old' => null, 'value' => 0,
            ],
            'hidden: HTML special characters' => [
                'kind' => 'hidden', 'old' => null, 'value' => 'He said "hi" <b> & </b>',
            ],
            'submit: text' => [
                'kind' => 'submit', 'old' => null, 'text' => 'Save',
            ],
            'submit: HTML special characters' => [
                'kind' => 'submit', 'old' => null, 'text' => 'Save & <go>',
            ],
            'label: text' => [
                'kind' => 'label', 'old' => null, 'text' => 'Title',
            ],
            'label: HTML special characters' => [
                'kind' => 'label', 'old' => null, 'text' => 'A & B <em>"quoted"</em>',
            ],
            'checkbox: value 1, default true' => [
                'kind' => 'checkbox', 'old' => null, 'value' => 1, 'default' => true,
            ],
            'checkbox: value 1, default false' => [
                'kind' => 'checkbox', 'old' => null, 'value' => 1, 'default' => false,
            ],
            'checkbox: value yes, default true' => [
                'kind' => 'checkbox', 'old' => null, 'value' => 'yes', 'default' => true,
            ],
            'checkbox: value true, default false' => [
                'kind' => 'checkbox', 'old' => null, 'value' => 'true', 'default' => false,
            ],
            'checkbox: old input 1' => [
                'kind' => 'checkbox', 'old' => ['n' => '1'], 'value' => 1, 'default' => false,
            ],
            'checkbox: old input 0' => [
                'kind' => 'checkbox', 'old' => ['n' => '0'], 'value' => 1, 'default' => true,
            ],
            'checkbox: old input empty string' => [
                'kind' => 'checkbox', 'old' => ['n' => ''], 'value' => 1, 'default' => true,
            ],
            'checkbox: old input without the key' => [
                'kind' => 'checkbox', 'old' => ['other' => 'value'], 'value' => 'yes', 'default' => true,
            ],
            'checkbox: null value, default true' => [
                'kind' => 'checkbox-null', 'old' => null, 'default' => true,
            ],
            'checkbox: null value, default false' => [
                'kind' => 'checkbox-null', 'old' => null, 'default' => false,
            ],
            'checkbox: null value, old input 1' => [
                'kind' => 'checkbox-null', 'old' => ['n' => '1'], 'default' => false,
            ],
            'checkbox: null value, old input without the key' => [
                'kind' => 'checkbox-null', 'old' => ['other' => 'value'], 'default' => true,
            ],
            'checkbox array: default contains the value' => [
                'kind' => 'checkbox-array', 'old' => null,
                'value' => '2_3', 'defaultArray' => ['2_3'], 'id' => 'm23',
            ],
            'checkbox array: default does not contain the value' => [
                'kind' => 'checkbox-array', 'old' => null,
                'value' => '2_4', 'defaultArray' => ['2_3'], 'id' => 'm24',
            ],
            'checkbox array: default contains a later value' => [
                'kind' => 'checkbox-array', 'old' => null,
                'value' => '5_7', 'defaultArray' => ['2_3', '5_7'], 'id' => 'm57',
            ],
            'checkbox array: empty default' => [
                'kind' => 'checkbox-array', 'old' => null,
                'value' => '2_3', 'defaultArray' => [], 'id' => 'm23',
            ],
            'checkbox array: composite value with double digits' => [
                'kind' => 'checkbox-array', 'old' => null,
                'value' => '12_34', 'defaultArray' => ['12_34'], 'id' => 'm1234',
            ],
            'checkbox array: old input contains the value' => [
                'kind' => 'checkbox-array', 'old' => ['media_checkbox' => ['2_3']],
                'value' => '2_3', 'defaultArray' => [], 'id' => 'm23',
            ],
            'checkbox array: old input does not contain the value' => [
                'kind' => 'checkbox-array', 'old' => ['media_checkbox' => ['2_4']],
                'value' => '2_3', 'defaultArray' => ['2_3'], 'id' => 'm23',
            ],
            'checkbox array: old input with several values' => [
                'kind' => 'checkbox-array', 'old' => ['media_checkbox' => ['2_3', '5_7']],
                'value' => '5_7', 'defaultArray' => [], 'id' => 'm57',
            ],
            'checkbox array: old input without the key overrides the default' => [
                'kind' => 'checkbox-array', 'old' => ['other' => 'value'],
                'value' => '2_3', 'defaultArray' => ['2_3'], 'id' => 'm23',
            ],
            'select: no old input, integer key selected' => [
                'kind' => 'select', 'old' => null,
                'options' => [1 => 'One', 'two' => 'Two', 0 => 'Zero'], 'selected' => 1,
            ],
            'select: no old input, string key selected' => [
                'kind' => 'select', 'old' => null,
                'options' => [1 => 'One', 'two' => 'Two', 0 => 'Zero'], 'selected' => 'two',
            ],
            'select: no old input, zero key selected' => [
                'kind' => 'select', 'old' => null,
                'options' => [1 => 'One', 'two' => 'Two', 0 => 'Zero'], 'selected' => 0,
            ],
            'select: old input value' => [
                'kind' => 'select', 'old' => ['bundle_id' => 'two'],
                'options' => [1 => 'One', 'two' => 'Two'], 'selected' => 1,
            ],
            'select: old input zero string' => [
                'kind' => 'select', 'old' => ['bundle_id' => '0'],
                'options' => [1 => 'One', 'two' => 'Two', 0 => 'Zero'], 'selected' => 1,
            ],
            'select: old input empty string' => [
                'kind' => 'select', 'old' => ['bundle_id' => ''],
                'options' => [1 => 'One', 'two' => 'Two'], 'selected' => 1,
            ],
            'select: old input without the key' => [
                'kind' => 'select', 'old' => ['other' => 'value'],
                'options' => [1 => 'One', 'two' => 'Two'], 'selected' => 'two',
            ],
            'select: default null' => [
                'kind' => 'select', 'old' => null,
                'options' => [1 => 'One', 'two' => 'Two'], 'selected' => null,
            ],
            'select: HTML special option labels' => [
                'kind' => 'select', 'old' => null,
                'options' => [1 => 'One & <Two>', 'two' => 'Two "quoted"'], 'selected' => 'two',
            ],
            'select multiple: integer keys selected' => [
                'kind' => 'select-multiple', 'old' => null,
                'options' => [1 => 'One', 'two' => 'Two', 10 => 'Ten'], 'selected' => [1, 10],
            ],
            'select multiple: no selection' => [
                'kind' => 'select-multiple', 'old' => null,
                'options' => [1 => 'One', 'two' => 'Two'], 'selected' => [],
            ],
            'select multiple: old input string value' => [
                'kind' => 'select-multiple', 'old' => ['bundle_id' => ['two']],
                'options' => [1 => 'One', 'two' => 'Two'], 'selected' => [1],
            ],
            'select multiple: old input integer-like values' => [
                'kind' => 'select-multiple', 'old' => ['bundle_id' => ['1', '10']],
                'options' => [1 => 'One', 'two' => 'Two', 10 => 'Ten'], 'selected' => [],
            ],
            'select multiple: old input empty array' => [
                'kind' => 'select-multiple', 'old' => ['bundle_id' => []],
                'options' => [1 => 'One', 'two' => 'Two'], 'selected' => [1],
            ],
            'select multiple: old input without the key' => [
                'kind' => 'select-multiple', 'old' => ['other' => 'value'],
                'options' => [1 => 'One', 'two' => 'Two'], 'selected' => ['two'],
            ],
            'select multiple: default null' => [
                'kind' => 'select-multiple', 'old' => null,
                'options' => [1 => 'One', 'two' => 'Two'], 'selected' => null,
            ],
            'open: multipart form with csrf token' => [
                'kind' => 'open', 'old' => null,
            ],
            'link: title' => [
                'kind' => 'link', 'old' => null, 'title' => 'Open list',
            ],
            'link: HTML special title' => [
                'kind' => 'link', 'old' => null, 'title' => 'A & B <em>"quoted"</em>',
            ],
            'image: alt' => [
                'kind' => 'image', 'old' => null, 'alt' => 'Preview image',
            ],
            'image: HTML special alt' => [
                'kind' => 'image', 'old' => null, 'alt' => 'A & B "quoted"',
            ],
        ];

        $data = [];

        foreach ($cases as $name => $case) {
            // PHPUnit 11 treats an associative data set as named arguments, so wrap each row.
            $data[$name] = [$case];
        }

        return $data;
    }

    /**
     * Flash the case's old input and make the request and FormBuilder read the same session store.
     */
    private function flashOldInput(?array $old): void
    {
        $session = app('session.store');
        $session->forget('_old_input');

        if ($old !== null) {
            $session->flashInput($old);
        }

        app('request')->setLaravelSession($session);
        \Form::setSessionStore($session);
    }

    /**
     * Render the real Collective helper for a case.
     */
    private function renderHelper(array $case): string
    {
        switch ($case['kind']) {
            case 'text':
                return (string) \Form::text('n', $case['value'], ['class' => 'form-control', 'required', 'id' => 'n']);
            case 'email':
                return (string) \Form::email('n', $case['value'], ['class' => 'form-control', 'required', 'id' => 'n']);
            case 'number':
                return (string) \Form::input('number', 'n', $case['value'], ['class' => 'form-control', 'min' => 0, 'id' => 'n']);
            case 'password':
                return (string) \Form::password('n', ['class' => 'form-control', 'id' => 'n']);
            case 'textarea':
                return (string) \Form::textarea('n', $case['value'], ['class' => 'form-control', 'rows' => 3, 'id' => 'n']);
            case 'hidden':
                return (string) \Form::hidden('n', $case['value'], ['id' => 'n']);
            case 'submit':
                return (string) \Form::submit($case['text'], ['class' => 'btn btn-primary']);
            case 'label':
                return (string) \Form::label('n', $case['text']);
            case 'checkbox':
                return (string) \Form::checkbox('n', $case['value'], $case['default'], ['class' => 'form-check-input', 'id' => 'n']);
            case 'checkbox-null':
                return (string) \Form::checkbox('n', null, $case['default'], ['class' => 'form-check-input', 'id' => 'n']);
            case 'checkbox-array':
                $checked = in_array($case['value'], $case['defaultArray']);

                return (string) \Form::checkbox('media_checkbox[]', $case['value'], $checked, ['id' => $case['id']]);
            case 'select':
                return (string) \Form::select('bundle_id', $case['options'], $case['selected'], ['class' => 'form-select', 'id' => 'bundle_id']);
            case 'select-multiple':
                return (string) \Form::select('bundle_id', $case['options'], $case['selected'], ['multiple', 'name' => 'bundle_id[]', 'class' => 'form-select', 'id' => 'bundle_id']);
            case 'open':
                return (string) \Form::open(['files' => true, 'action' => self::FORM_ACTION, 'role' => 'form'])
                    . (string) \Form::close();
            case 'link':
                return (string) \HTML::link('admin/x', $case['title'], ['class' => 'btn btn-link btn-sm']);
            case 'image':
                return (string) \HTML::image('img/x.png', $case['alt'], ['class' => 'img-fluid']);
        }

        $this->fail('Unknown helper kind: ' . $case['kind']);
    }

    /**
     * Render the plain-Blade snippet for a case.
     */
    private function renderSnippet(array $case): string
    {
        switch ($case['kind']) {
            case 'text':
                return Blade::render($this->snippet(self::TEXT_SNIPPET), ['v' => $case['value']]);
            case 'email':
                return Blade::render($this->snippet(self::EMAIL_SNIPPET), ['v' => $case['value']]);
            case 'number':
                return Blade::render($this->snippet(self::NUMBER_SNIPPET), ['v' => $case['value']]);
            case 'password':
                return Blade::render($this->snippet(self::PASSWORD_SNIPPET));
            case 'textarea':
                return Blade::render($this->snippet(self::TEXTAREA_SNIPPET), ['v' => $case['value']]);
            case 'hidden':
                return Blade::render($this->snippet(self::HIDDEN_SNIPPET), ['v' => $case['value']]);
            case 'submit':
                return Blade::render($this->snippet(self::SUBMIT_SNIPPET), ['text' => $case['text']]);
            case 'label':
                return Blade::render($this->snippet(self::LABEL_SNIPPET), ['text' => $case['text']]);
            case 'checkbox':
                return Blade::render($this->snippet(self::CHECKBOX_SNIPPET), [
                    'value' => $case['value'],
                    'default' => $case['default'],
                ]);
            case 'checkbox-null':
                return Blade::render($this->snippet(self::CHECKBOX_NULL_SNIPPET), ['default' => $case['default']]);
            case 'checkbox-array':
                return Blade::render($this->snippet(self::CHECKBOX_ARRAY_SNIPPET), [
                    'value' => $case['value'],
                    'id' => $case['id'],
                    'defaultArray' => $case['defaultArray'],
                ]);
            case 'select':
                return Blade::render($this->snippet(self::SELECT_SNIPPET), [
                    'options' => $case['options'],
                    'selected' => $case['selected'],
                ]);
            case 'select-multiple':
                return Blade::render($this->snippet(self::SELECT_MULTIPLE_SNIPPET), [
                    'options' => $case['options'],
                    'selected' => $case['selected'],
                ]);
            case 'open':
                return Blade::render($this->snippet(self::OPEN_SNIPPET), ['url' => action(self::FORM_ACTION)]);
            case 'link':
                return Blade::render($this->snippet(self::LINK_SNIPPET), ['title' => $case['title']]);
            case 'image':
                return Blade::render($this->snippet(self::IMAGE_SNIPPET), ['alt' => $case['alt']]);
        }

        $this->fail('Unknown snippet kind: ' . $case['kind']);
    }

    /**
     * Remove only the trailing newline that keeps the nowdoc readable.
     */
    private function snippet(string $snippet): string
    {
        return rtrim($snippet);
    }

    /**
     * Parse a rendered fragment into a canonical, order-insensitive DOM structure.
     */
    private function normalise(string $html): array
    {
        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();

        $document = new DOMDocument('1.0', 'UTF-8');
        $loaded = $document->loadHTML(
            '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>'
            . '<body><div id="__root">' . $html . '</div></body></html>',
            LIBXML_NONET
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $this->assertTrue($loaded, 'Could not parse rendered markup: ' . $html);

        $root = (new DOMXPath($document))->query('//*[@id="__root"]')->item(0);
        $this->assertNotNull($root, 'Could not find the wrapper when parsing: ' . $html);

        $nodes = [];

        foreach ($root->childNodes as $child) {
            $node = $this->normaliseNode($child);

            if ($node !== null) {
                $nodes[] = $node;
            }
        }

        return $nodes;
    }

    /**
     * Canonicalise one node: tag, sorted attributes, exact text, and child elements.
     */
    private function normaliseNode(DOMNode $node): ?array
    {
        if ($node instanceof DOMElement) {
            $tag = strtolower($node->nodeName);
            $attributes = [];

            foreach ($node->attributes as $attribute) {
                $name = strtolower($attribute->name);
                $value = $attribute->value;

                if ($this->isIgnoredAttribute($tag, $name, $value)) {
                    continue;
                }

                if (in_array($name, self::BOOLEAN_ATTRIBUTES, true) && ($value === '' || $value === $name)) {
                    $value = $name;
                }

                $attributes[$name] = $value;
            }

            ksort($attributes);

            $children = [];

            foreach ($node->childNodes as $child) {
                $normalised = $this->normaliseNode($child);

                if ($normalised !== null) {
                    $children[] = $normalised;
                }
            }

            return [
                'tag' => $tag,
                'attributes' => $attributes,
                'children' => $children,
            ];
        }

        if ($node instanceof DOMText) {
            $text = $node->nodeValue;

            if ($text === '') {
                return null;
            }

            if (trim($text) === '' && $this->isBetweenElements($node)) {
                return null;
            }

            return ['text' => $text];
        }

        return null;
    }

    /**
     * Ignore only the intentional markup differences listed in the migration plan.
     */
    private function isIgnoredAttribute(string $tag, string $name, string $value): bool
    {
        if ($tag === 'form' && $name === 'accept-charset') {
            return true;
        }

        // An empty value attribute means the same as none (text inputs with a null default, password).
        if ($tag === 'input' && $name === 'value' && $value === '') {
            return true;
        }

        if ($tag === 'textarea' && $name === 'cols' && $value === '50') {
            return true;
        }

        if ($tag === 'textarea' && $name === 'rows' && $value === '10') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether a whitespace-only text node is only formatting between elements.
     */
    private function isBetweenElements(DOMText $node): bool
    {
        return $node->previousSibling instanceof DOMElement || $node->nextSibling instanceof DOMElement;
    }
}
