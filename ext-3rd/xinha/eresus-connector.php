<?php
/**
 * ${product.title} ${product.version}
 *
 * ${product.description}
 *
 * @copyright 2004, ProCreat Systems, http://procreat.ru/
 * @copyright 2007, Eresus Project, http://eresus.ru/
 * @license ${license.uri} ${license.name}
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 *
 * ������ ��������� �������� ��������� ����������� ������������. ��
 * ������ �������������� �� �/��� �������������� � ������������ �
 * ��������� ������ 3 ���� (�� ������ ������) � ��������� ����� �������
 * ������ ����������� ������������ �������� GNU, �������������� Free
 * Software Foundation.
 *
 * �� �������������� ��� ��������� � ������� �� ��, ��� ��� ����� ���
 * ��������, ������ �� ������������� �� ��� ������� ��������, � ���
 * ����� �������� ��������� ��������� ��� ������� � ����������� ���
 * ������������� � ���������� �����. ��� ��������� ����� ���������
 * ���������� ������������ �� ����������� ������������ ��������� GNU.
 *
 * �� ������ ���� �������� ����� ����������� ������������ ��������
 * GNU � ���� ����������. ���� �� �� �� ��������, �������� �������� ��
 * <http://www.gnu.org/licenses/>
 *
 * @package EresusCMS
 *
 * $Id$
 */

/**
 * �����-���������
 *
 */
class XinhaConnector extends EresusExtensionConnector
{
	function forms_html($form, $field)
	{
		global $Eresus, $page, $locale;

    $value = isset($form->values[$field['name']]) ? $form->values[$field['name']] : (isset($field['value'])?$field['value']:'');
    $result = "\t\t".'<tr><td colspan="2">'.$field['label'].'<br /><textarea name="wyswyg_'.$field['name'].'" id="wyswyg_'.$field['name'].'" style="width: 100%; height: '.$field['height'].';">'.str_replace('$(httpRoot)', $Eresus->root, EncodeHTML($value)).'</textarea></td></tr>'."\n";

    $page->addScripts(
      'var _editor_url  = "'.$this->root.'";'."\n".
      'var _editor_lang = "'.$locale['lang'].'";'."\n".
      'var _editor_skin = "";'."\n".
      "var xinha_editors = ['wyswyg_".$field['name']."'];\n"
    );
    $page->linkScripts($this->root.'htmlarea.js');
    $page->linkScripts($this->root.'editor.js');

		return $result;
	}
	//-----------------------------------------------------------------------------
}

?>