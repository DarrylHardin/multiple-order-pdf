<?php
/**
 * Multiple Order PDF plugin for Craft CMS 3.x
 *
 * Put multiple selected orders on a single PDF page, the order will be pushed to next page if page break would split.
 *
 * @link      https://importantcoding.com
 * @copyright Copyright (c) 2019 importantcoding
 */

namespace importantcoding\multipleorderpdf\models;

use importantcoding\multipleorderpdf\MultipleOrderPdf;

use Craft;
use craft\base\Model;

/**
 * Settings Model
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    importantcoding
 * @package   MultipleOrderPdf
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Some model attribute
     *
     * @var string
     */
    public $someAttribute = 'Some Default';

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['someAttribute', 'string'],
            ['someAttribute', 'default', 'value' => 'Some Default'],
        ];
    }
}
