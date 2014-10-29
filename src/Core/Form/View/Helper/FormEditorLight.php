<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013-2014 Cross Solution (http://cross-solution.de)
 * @license   MIT
 * @author    weitz@cross-solution.de
 */

namespace Core\Form\View\Helper;

class FormEditorLight extends FormEditor
{
    protected $theme = 'light';

    protected function additionalOptions() {
        return '
        plugins: [
            "advlist autolink lists charmap anchor",
            "searchreplace visualblocks code fullscreen",
            "contextmenu paste"
        ],
        toolbar: "undo redo ",
        removed_menuitems: "newdocument",
        ';

        //     | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent
    }

}