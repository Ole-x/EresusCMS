<?php
/**
 * HTML-��������
 *
 * Eresus 2
 *
 * ������ ������������ ���������� �������������� ����������������� �������
 *
 * @version 3.00
 *
 * @copyright 	2005-2006, ProCreat Systems, http://procreat.ru/
 * @copyright   2007, Eresus Group, http://eresus.ru/
 * @license     http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @maintainer  ����� <bersz@procreat.ru>
 * @author      Mikhail Krasilnikov <mk@procreat.ru>
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
 */

class Html extends ContentPlugin {
  var $version = '3.00a2';
  var $kernel = '2.10b2';
  var $title = 'HTML';
  var $description = 'HTML ��������';
  var $type = 'client,content,ondemand';
 /**
  * ���������� ��������
  *
  * @param string $content  ����� �������
  */
  function updateContent($content)
  {
		global $Eresus, $page;

	  $item = $Eresus->db->selectItem('pages', "`id`='".$page->id."'");
	  $item['content'] = $content;
		$item['options'] = decodeOptions($item['options']);
		$item['options']['allowGET'] = arg('allowGET');
		$item['options'] = encodeOptions($item['options']);
	  $Eresus->db->updateItem('pages', $item, "`id`='".$page->id."'");
  }
	//------------------------------------------------------------------------------
 /**
  * ��������� ���������������� �����
  *
	* @return  string  �������
	*/
  function adminRenderContent()
  {
		global $page, $Eresus;

	  if (arg('action') == 'update') $this->adminUpdate();
		$item = $Eresus->sections->get($page->id);
		$url = $page->clientURL($item['id']);
	  $form = array(
		'name' => 'contentEditor',
		'caption' => $page->title,
		'width' => '100%',
		'fields' => array (
		  array ('type'=>'hidden','name'=>'action', 'value' => 'update'),
				array ('type' => 'html','name' => 'content','height' => '400px', 'value'=>$item['content']),
				array ('type' => 'text', 'value' => '����� ��������: <a href="'.$url.'">'.$url.'</a>'),
				array ('type' => 'checkbox','name' => 'allowGET', 'label' => '��������� ���������� ��������� � ������ ��������', 'value'=>isset($item['options']['allowGET'])?$item['options']['allowGET']:false),
		 ),
		'buttons' => array('apply', 'reset'),
	  );

	  $result = $page->renderForm($form, $item);
	  return $result;
  }
	//------------------------------------------------------------------------------
  function clientRenderContent()
  {
		global $request, $page;
		if ($page->topic) {
	  	if (!(isset($page->options['allowGET']) && $page->options['allowGET'] && (strpos($page->topic, execScript.'?') === 0))) $page->httpError(404);
		}
		return parent::clientRenderContent();
  }
	//------------------------------------------------------------------------------
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
?>