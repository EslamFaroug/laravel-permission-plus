<?php
namespace EslamFaroug\PermissionPlus\Tests\Unit;


use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\Model;
use EslamFaroug\PermissionPlus\Traits\HasTranslatable;

class DummyTranslatableModel extends Model {
    use HasTranslatable;
    protected $translatable = ['name', 'desc'];
    public $attributes = [];
    public function getAttribute($k) { return $this->attributes[$k] ?? null; }
    public function setAttribute($k, $v) { $this->attributes[$k] = $v; }
}

class HasTranslatableTest extends TestCase
{
    public function test_returns_correct_translation_for_current_locale()
    {
        $model = new DummyTranslatableModel();
        $model->setAttribute('name', json_encode(['en' => 'Admin', 'ar' => 'مشرف', 'fr' => 'Administrateur']));
        app()->setLocale('ar');
        $this->assertEquals('مشرف', $model->name);
        app()->setLocale('en');
        $this->assertEquals('Admin', $model->name);
        app()->setLocale('fr');
        $this->assertEquals('Administrateur', $model->name);
    }

    public function test_returns_first_available_translation_if_locale_missing()
    {
        $model = new DummyTranslatableModel();
        $model->setAttribute('name', json_encode(['es' => 'Administrador']));
        app()->setLocale('ar');
        $this->assertEquals('Administrador', $model->name);
    }

    public function test_get_translations_returns_array()
    {
        $model = new DummyTranslatableModel();
        $model->setAttribute('desc', json_encode(['en' => 'Description', 'ar' => 'الوصف']));
        $all = $model->getTranslations('desc');
        $this->assertEquals(['en' => 'Description', 'ar' => 'الوصف'], $all);
    }

    public function test_setter_stores_translation_as_json()
    {
        $model = new DummyTranslatableModel();
        $model->desc = ['en' => 'Test', 'ar' => 'اختبار'];
        $stored = $model->getAttribute('desc');
        $this->assertEquals(['en' => 'Test', 'ar' => 'اختبار'], json_decode($stored, true));
    }
}
