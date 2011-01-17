/**
 * ${product.title} ${product.version}
 *
 * ${product.description}
 *
 * @copyright 2004, ProCreat Systems, http://procreat.ru/
 * @copyright 2007, Eresus Project, http://eresus.ru/
 * @license ${license.uri} ${license.name}
 * @author Mikhail Krasilnikov <mihalych@vsepofigu.ru>
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
 * $Id$
 */

var isIE = (navigator.userAgent.toLowerCase().indexOf('msie') != -1) && (navigator.userAgent.toLowerCase().indexOf('opera') == -1);
var HttpRequest = null;
var BrowseFileLast = '';

//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
function replaceMacros(sURL)
{
	var macros = new Array();
	macros['httpRoot'] = '$(httpRoot)';
	macros['httpHost'] = '$(httpHost)';
	macros['httpPath'] = '$(httpPath)';
	macros['styleRoot'] = '$(styleRoot)';
	macros['dataRoot'] = '$(dataRoot)';

	function __replace(sMatch, sMacros)
	{
		return macros[sMacros];
	}

	sURL = sURL.replace(/\$\(([^\)]+)\)/, __replace);
	return sURL;
}
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
function pageLeft()
{
	return isIE ? (document.body.scrollLeft?document.body.scrollLeft:document.documentElement.scrollLeft) : window.pageXOffset;
}
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
function pageTop()
{
	return isIE ? (document.body.scrollTop?document.body.scrollTop:document.documentElement.scrollTop) : window.pageYOffset;
}
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
function askdel(objCaller)
{
	return confirm('������������ ��������?');
}
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
function formApplyClick(strForm)
{
	var objForm = document.forms[strForm];
	objForm.submitURL.value = document.URL;
}
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
function BrowseFileDialog(id, Folder)
{
	var hnd = window.open('$(httpRoot)core/dlg/BrowseFile.php?id='+id+'&root='+BrowseFileLast, 'OpenFileDialog', 'dependent=yes,width=500,height=550,resizable=yes,menubar=no,directories=no,personalbar=no,scrollbars=no,status=no,titlebar=no,toolbar=no');
}
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
function SendRequest(url, handler)
{
	// branch for native XMLHttpRequest object
	if (window.XMLHttpRequest) {
		HttpRequest = new XMLHttpRequest();
		HttpRequest.onreadystatechange = handler;
		HttpRequest.open('GET', url, true);
		HttpRequest.send(null);
	// branch for IE/Windows ActiveX version
	} else if (window.ActiveXObject) {
		HttpRequest = new ActiveXObject('Microsoft.XMLHTTP');
		if (HttpRequest) {
			HttpRequest.onreadystatechange = handler;
			HttpRequest.open('GET', url, true);
			HttpRequest.send();
		}
	}
}
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
