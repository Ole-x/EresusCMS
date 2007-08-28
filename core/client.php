<?php
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
# ������� ���������� ��������� Eresus�
# ������ 2.10
# � 2004-2007, ProCreat Systems
# � 2007, Eresus Group
# http://eresus.ru/
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
# ��������� ����������
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
define('CLIENTUI', true);

# ���������� ���� ������� #
$filename = dirname(__FILE__).DIRECTORY_SEPARATOR.'kernel.php';
if (is_file($filename)) include_once($filename); else {
  echo "<h1>Fatal error</h1>\n<strong>Kernel not available!</strong><br />\nThis error can take place during site update.<br />\nPlease try again later.";
  exit;
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
function __macroConst($matches) {
  return constant($matches[1]);
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
function __macroVar($matches) {
  $result = $GLOBALS[$matches[2]];
  if (!empty($matches[3])) @eval('$result = $result'.$matches[3].';');
  return $result;
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#

#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
# ����� "��������"
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
class TClientUI {
  var $dbItem = array(); # ���������� � �������� �� ��
  var $id = -1; # ������������� ��������
  var $name = ''; # ��� ��������
  var $owner = 0; # ������������� ������������ ��������
  var $title = ''; # ��������� ��������
  var $section = array(); # ������ ���������� �������
  var $caption = ''; # �������� ��������
  var $hint = ''; # ��������� � ��������� ��������
  var $description = ''; # �������� ��������
  var $keywords = ''; # �������� ��������
  var $access = GUEST; # ������� ������� ������� � ��������
  var $visible = true; # ��������� ��������
  var $type = 'default'; # ��� ��������
  var $content = ''; # ������� ��������
  var $options = array(); # ����� ��������
  var $Document; # DOM-��������� � ��������
  var $plugin; # ������ ��������
  var $headers; # ��������� ������ �������
  var $scripts = ''; # �������
  var $styles = ''; # �����
  var $subpage = 0; # �����
  //------------------------------------------------------------------------------
  /**
  * �����������
  *
  * @access  public
  */
  function TClientUI()
  {
    global $Eresus;
    
    useLib('sections');
    $Eresus->sections = new TSections;
  }
  //------------------------------------------------------------------------------
  # ���������� �������
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------# 
  function replaceMacros($text)
  # ����������� �������� ��������
  {
  global $user;
  
  $section = $this->section;
  if (siteTitleReverse) $section = array_reverse($section);
  $section = strip_tags(implode($section, option('siteTitleDivider')));
  
  $result = str_replace(
      array(
        '$(httpHost)',
        '$(httpPath)',
        '$(httpRoot)',
        '$(styleRoot)',
        '$(dataRoot)',
        
        '$(siteName)',
        '$(siteTitle)',
        '$(siteKeywords)',
        '$(siteDescription)',
        
        '$(pageId)',
        '$(pageName)',
        '$(pageTitle)',
        '$(pageCaption)',
        '$(pageHint)',
        '$(pageDescription)',
        '$(pageKeywords)',
        '$(pageAccessLevel)',
        '$(pageAccessName)',

        '$(sectionTitle)',
      ),
      array(
        httpHost, 
        httpPath, 
        httpRoot, 
        styleRoot,
        dataRoot,
        
        siteName,
        siteTitle,
        siteKeywords,
        siteDescription,
        
        $this->id,
        $this->name,
        $this->title,
        $this->caption,
        $this->hint,
        $this->description,
        $this->keywords,
        $this->access,
        constant('ACCESSLEVEL'.$this->access),
        $section,
      ),
      $text
    );
    $result = preg_replace_callback('/\$\(const:(.*?)\)/i', '__macroConst', $result);
    $result = preg_replace_callback('/\$\(var:(([\w]*)(\[.*?\]){0,1})\)/i', '__macroVar', $result);
    $result = preg_replace('/\$\(\w+(:.*?)*?\)/', '', $result);
    return $result;
  } 
  //------------------------------------------------------------------------------
  /**
  * ���������� ������ URL � �������� ���������������� �������
  *
  * @access  private
  *
  * @return  array|bool  �������� ������������ ������� ��� false ���� �� �� ������
  */
  function loadPage()
  {
    global $Eresus, $plugins, $request, $user;
    
    $result = false;
    if (!count($Eresus->request['params']) || $Eresus->request['params'][0] != 'main') {
      array_unshift($Eresus->request['params'], 'main');
      $request['params'] = $Eresus->request['params'];
    }
    reset($Eresus->request['params']);
    $item['id'] = 0;
    $url = '';
    do {
      $items = $Eresus->sections->children($item['id'], $user['auth']?$user['access']:GUEST, SECTIONS_ACTIVE);
      $item = false;
      for($i=0; $i<count($items); $i++) if ($items[$i]['name'] == current($Eresus->request['params'])) {
        $result = $item = $items[$i];
        if ($item['name'] != 'main' || !empty($url)) $url .= $item['name'].'/';
        $plugins->clientOnURLSplit($item, $url);
        $this->section[] = $item['title'];
        next($Eresus->request['params']);
        array_shift($request['params']);
      }
    } while ($item && current($Eresus->request['params']));
    $request['path'] = $Eresus->request['path'] = $Eresus->root.$url;
    if ($result) $result = $Eresus->sections->get($result['id']);
    return $result;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------# 
  # ����� �������
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------# 
  function init()
  # �������� ������������� ��������
  {
  global $db, $user, $plugins, $request;

    $plugins->preload(array('client'),array('ondemand'));
    $plugins->clientOnStart();
    
    $item = $this->loadPage();
    if ($item) {
      if (count($request['params'])) {
        if (preg_match('/p[\d]+/i', $request['params'][0])) $this->subpage = substr(array_shift($request['params']), 1);
        if (count($request['params'])) $this->topic = array_shift($request['params']);
      }
      $this->dbItem = $item;
      $this->id = $item['id'];
      $this->name = $item['name'];
      $this->owner = $item['owner'];
      $this->title = $item['title'];
      $this->description = $item['description'];
      $this->keywords = $item['keywords'];
      $this->caption = $item['caption'];
      $this->hint = $item['hint'];
      $this->access = $item['access'];
      $this->visible = $item['visible'];
      $this->type = $item['type'];
      $this->template = $item['template'];
      $this->created = $item['created'];
      $this->updated = $item['updated'];
      $this->content = $item['content'];
      $this->scripts = '';
      $this->styles = '';
      $this->options = decodeOptions($item['options']);
    } else $this->httpError(404);
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------# 
  function Error404()
  {
    $this->httpError(404);
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------# 
  function httpError($code)
  {
  global $KERNEL;
  
    if (isset($KERNEL['ERROR'])) return;
    $ERROR = array(
      '400' => array('response' => 'Bad Request'),
      '401' => array('response' => 'Unauthorized'),
      '402' => array('response' => 'Payment Required'),
      '403' => array('response' => 'Forbidden'),
      '404' => array('response' => 'Not Found'),
      '405' => array('response' => 'Method Not Allowed'),
      '406' => array('response' => 'Not Acceptable'),
      '407' => array('response' => 'Proxy Authentication Required'),
      '408' => array('response' => 'Request Timeout'),
      '409' => array('response' => 'Conflict'),
      '410' => array('response' => 'Gone'),
      '411' => array('response' => 'Length Required'),
      '412' => array('response' => 'Precondition Failed'),
      '413' => array('response' => 'Request Entity Too Large'),
      '414' => array('response' => 'Request-URI Too Long'),
      '415' => array('response' => 'Unsupported Media Type'),
      '416' => array('response' => 'Requested Range Not Satisfiable'),
      '417' => array('response' => 'Expectation Failed'),
    );
  
    Header($_SERVER['SERVER_PROTOCOL'].' '.$code.' '.$ERROR[$code]['response']);

    if (defined('HTTP_CODE_'.$code)) $message = constant('HTTP_CODE_'.$code);
    else $message = $ERROR[$code]['response'];

    $this->section = array(siteTitle, $message);
    $this->title = $message;
    $this->description = '';
    $this->keywords = '';
    $this->caption = $message;
    $this->hint = '';
    $this->access = GUEST;
    $this->visible = true;
    $this->type = 'default';
    if (file_exists(filesRoot.'templates/std/'.$code.'.tmpl')) {
      $this->template = 'std/'.$code;
      $this->content = '';
    } else {
      $this->template = 'default';
      $this->content = '<h1>HTTP ERROR '.$code.': '.$message.'</h1>';
    }
    $KERNEL['ERROR'] = true;
    $this->render();
    exit;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------# 
  function url($dummy=null)
  {
    global $request;
    
    $pos = strpos($request['url'], '?');
    $result = ($pos === false) ? $request['url'] : substr($request['url'], 0, $pos);
    return $result;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------# 
  function clientURL($id)
  # ������� ���������� HTTP ���� � �������� � ��������������� $id
  {
    global $db;
    
    $result = '';
    $item = $db->selectItem('pages', "`id`='".$id."'");
    while (!is_null($item)) {
      $result = $item['name'].'/'.$result;
      $item = $db->selectItem('pages', "`id`='".$item['owner']."'");
    }
    if ($result == 'main/') $result = '';
    $result = httpRoot.$result;
    return $result;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------# 
  function render()
  # ���������� ��������� �������� ������������.
  {
    global $Eresus, $KERNEL, $plugins, $session, $request;

    if (isset($request['arg']['HTTP_ERROR'])) $this->httpError($request['arg']['HTTP_ERROR']);
    # ������������ �������
    $content = $plugins->clientRenderContent();
    #$this->updated = mktime(substr($this->updated, 11, 2), substr($this->updated, 14, 2), substr($this->updated, 17, 2), substr($this->updated, 5, 2), substr($this->updated, 8, 2), substr($this->updated, 0, 4));
    #if ($this->updated < 0) $this->updated = 0;
    #$this->headers[] = 'Last-Modified: ' . gmdate('D, d M Y H:i:s', $this->updated) . ' GMT';
    $this->headers[] = 'Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT';
    $template = filesRoot.'templates/'.$this->template.'.tmpl';
    if (file_exists($template)) $template = file_get_contents($template); else {
      $template = filesRoot.'templates/default.tmpl';
      if (file_exists($template)) $template = file_get_contents($template); else FatalError('File not found', 'Open file '.$template);
    }
    $this->template = trim(substr($template, strpos($template, "\n")));
    $content = $plugins->clientOnContentRender($content);

    if (isset($session['msg']['information']) && count($session['msg']['information'])) {
      $messages = '';
      foreach($session['msg']['information'] as $message) $messages .= InfoBox($message);
      $content = $messages.$content;
      $session['msg']['information'] = array();
    }
    if (isset($session['msg']['errors']) && count($session['msg']['errors'])) {
      $messages = '';
      foreach($session['msg']['errors'] as $message) $messages .= ErrorBox($message);
      $content = $messages.$content;
      $session['msg']['errors'] = array();
    }
    $result = str_replace('$(Content)', $content, $this->template);
    
    if (!empty($this->styles)) {
      $styles = "<style type=\"text/css\">\n  ".str_replace("\n", "\n  ", trim($this->styles))."\n</style>\n";
      $result = preg_replace('|(.*)</head>|i', '$1'.$styles."\n</head>", $result);
    }

    $result = $plugins->clientOnPageRender($result);

    if (!empty($this->scripts)) $this->scripts = "  <script type=\"text/javascript\">\n  //<!-- <![CDATA[\n  ".str_replace("\n", "\n    ", trim($this->scripts))."\n  //]] -->\n  </script>\n";
    $this->scripts =
      '  <script type="text/javascript">'."\n".
      "  //<!-- <![CDATA[\n".
      "    var iBrowser = new Array();\n".
      "    iBrowser['UserAgent'] = navigator.userAgent.toLowerCase();\n".
      "    if ((iBrowser['UserAgent'].indexOf('msie') != -1) && (iBrowser['UserAgent'].indexOf('opera') == -1) && (iBrowser['UserAgent'].indexOf('webtv') == -1)) iBrowser['Engine'] = 'IE';\n".
      "    if (iBrowser['UserAgent'].indexOf('gecko') != -1) iBrowser['Engine'] = 'Gecko';\n".
      "    if (iBrowser['UserAgent'].indexOf('opera') != -1) iBrowser['Engine'] = 'Opera';\n".
      "    if (iBrowser['UserAgent'].indexOf('safari') != -1) iBrowser['Engine'] = 'Safari';\n".
      "    if (iBrowser['UserAgent'].indexOf('konqueror') != -1) iBrowser['Engine'] = 'Konqueror';\n".
      "    iBrowser['UserAgent'] = navigator.userAgent;\n".
      "  //]] -->".
      "  </script>\n".
      $this->scripts;
    $result = preg_replace('|(.*)</head>|i', '$1'.$this->scripts."\n</head>", $result);
    # ������ ��������
    $result = $this->replaceMacros($result);

    if (count($this->headers)) foreach ($this->headers as $header) Header($header);
    
    $result = $plugins->clientBeforeSend($result);
    if (!$Eresus->conf['debug']['enable']) ob_start('ob_gzhandler');
    echo $result;
    if (!$Eresus->conf['debug']['enable']) ob_end_flush();
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------# 
  function pages($pagesCount, $itemsPerPage, $reverse = false)
  # ������� ������ ���������� ��� ��������� �� ���
  {
  global $request;

    if ($pagesCount>1) {
      $at_once = option('clientPagesAtOnce');
      if (!$at_once) $at_once = 10;
      
      $side_left = '';
      $side_right = '';
      
      $for_from = $reverse ? $pagesCount : 1;
      $default = $for_from;
      $for_to = $reverse ? 0 : $pagesCount+1;
      $for_delta = $reverse ? -1 : 1;

      # ���� ���������� ������� ��������� AT_ONCE
      if ($pagesCount > $at_once) {
        if ($reverse) { # ���� ���������� �������� ������� �������
          if ($this->subpage < ($pagesCount - (integer)($at_once / 2))) $for_from = ($this->subpage + (integer)($at_once / 2));
          if ($this->subpage < (integer)($at_once / 2)) $for_from = $at_once;
          $for_to = $for_from - $at_once;
          if ($for_to < 0) {$for_from += abs($for_to); $for_to = 0;}
          if ($for_from != $pagesCount) $side_left = "<a href=\"".$request['path']."\" title=\"".strLastPage."\">&nbsp;&laquo;&nbsp;</a>";
          if ($for_to != 0) $side_right = "<a href=\"".$request['path']."p1/\" title=\"".strFirstPage."\">&nbsp;&raquo;&nbsp;</a>";
        } else { # ���� ���������� ������ ������� �������
          if ($this->subpage > (integer)($at_once / 2)) $for_from = $this->subpage - (integer)($at_once / 2); 
          if ($pagesCount - $this->subpage < (integer)($at_once / 2) + (($at_once % 2)>0)) $for_from = $pagesCount - $at_once+1;
          $for_to = $for_from + $at_once;
          if ($for_from != 1) $side_left = "<a href=\"".$request['path']."\" title=\"".strFirstPage."\">&nbsp;&laquo;&nbsp;</a>";
          if ($for_to < $pagesCount) $side_right = "<a href=\"".$request['path']."p".$pagesCount."/\" title=\"".strLastPage."\">&nbsp;&raquo;&nbsp;</a>";
        }
      }
      $result = '<div class="pages">'.strPages;
      $result .= $side_left;
      for ($i = $for_from; $i != $for_to; $i += $for_delta) 
        if ($i == $this->subpage) $result .= '<span class="selected">&nbsp;'.$i.'&nbsp;</span>';
          else $result .= '<a href="'.$request['path'].($i==$default?'':'p'.$i.'/').'">&nbsp;'.$i.'&nbsp;</a>';
      $result .= $side_right;
      $result .= "</div>\n";
      return $result;
    } 
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------# 
  function renderForm($form, $values=null)
  { 
  global $request;
  
    $result = '';
    $hidden = '';
    $body = '';
    $validator = '';
    $html = false;
    $file = false;
    if (empty($form['name'])) ErrorMessage(errFormHasNoName);
    if (count($form['fields'])) foreach($form['fields'] as $item) {
      if ((!isset($item['access'])) || (UserRights($item['access']))) {
        if (isset($item['label'])) $label = !empty($item['hint']) ? '<span class="hint" title="'.$item['hint'].'">'.$item['label'].'</span>': $item['label']; else $label = '';
        if (isset($item['pattern'])) $validator .= "if (!form.".$item['name'].".value.match(".$item['pattern'].")) {\nalert('".(empty($item['errormsg'])?sprintf(errFormPatternError, $item['name'], $item['pattern']):$item['errormsg'])."');\nresult = false;\nform.".$item['name'].".select();\n} else ";
        $value = 
          isset($item['value'])
            ? $item['value']
            : (isset($item['name']) && isset($values[$item['name']])
                ? $values[$item['name']] 
                : (isset($item['default'])
                    ? $item['default']
                    : ''
                  )
              );
        $width = isset($item['width'])?' style="width: '.$item['width'].';"':'';
        $disabled = isset($item['disabled']) && $item['disabled']?' disabled':'';
        $extra = isset($item['extra'])?' '.$item['extra']:'';
        $comment = isset($item['comment'])?' '.$item['comment']:'';
        switch(strtolower($item['type'])) {
          case 'hidden': 
            if (empty($item['name'])) ErrorMessage(sprintf(errFormFieldHasNoName, $item['type'], $form['name']));
            $hidden .= '<input type="hidden" name="'.$item['name'].'" value="'.$value.'" />'."\n";
          break;
          case 'divider': $body .= "<tr><td colspan=\"2\"><hr></td></tr>\n"; break;
          case 'text': $body .= '<tr><td colspan="2" class="formText"'.$extra.'>'.$value."</td></tr>\n"; break;
          case 'header': $body .= '<tr><th colspan="2" class="formHeader">'.$value."</th></tr>\n"; break;
          case 'edit': 
            if (empty($item['name'])) ErrorMessage(sprintf(errFormFieldHasNoName, $item['type'], $form['name']));
            $body .= '<tr><td class="formLabel">'.$label.'</td><td><input type="text" name="'.$item['name'].'" value="'.EncodeHTML($value).'"'.(empty($item['maxlength'])?'':' maxlength="'.$item['maxlength'].'"').$width.$disabled.$extra.' />'.$comment."</td></tr>\n"; break;
          break;
          case 'password': 
            if (empty($item['name'])) ErrorMessage(sprintf(errFormFieldHasNoName, $item['type'], $form['name']));
            $body .= '<tr><td class="formLabel">'.$label.'</td><td><input type="password" name="'.$item['name'].'"'.(empty($item['maxlength'])?'':' maxlength="'.$item['maxlength']).'"'.$width.$extra.' />'.$comment."</td></tr>\n";
            if (isset($item['equal'])) $validator .= "if (form.".$item['name'].".value != form.".$item['equal'].".value) {\nalert('".errFormBadConfirm."');\nresult = false;\nform.".$item['name'].".value = '';\nform.".$item['equal'].".value = ''\nform.".$item['equal'].".select();\n} else ";
          break;
          case 'select': 
            if (empty($item['name'])) ErrorMessage(sprintf(errFormFieldHasNoName, $item['type'], $form['name']));
            $body .= '<tr><td class="formLabel">'.$label.'</td><td><select name="'.$item['name'].'"'.$width.$disabled.$extra.'>'."\n";
            if (!isset($item['items']) && isset($item['values'])) $item['items'] = $item['values'];
            for($i = 0; $i < count($item['items']); $i++) {
              if (isset($item['values'])) $value = $item['values'][$i]; else $value = $i;
              $body .= '<option value="'.$value.'" '.($value == (isset($values[$item['name']]) ? $values[$item['name']] : (isset($item['value'])?$item['value']:'')) ? 'selected="selected"' : '').">".$item['items'][$i]."</option>\n";
            }
            $body .= '</select>'.$comment."</td></tr>\n";
          break;
          case 'listbox':
            if (empty($item['name'])) ErrorMessage(sprintf(errFormFieldHasNoName, $item['type'], $form['name']));
            $body .= '<tr><td class="formLabel">'.$label.'</td><td><select multiple="multiple" name="'.$item['name'].'[]"'.$width.(isset($item['height'])?' size="'.$item['height'].'"':'').$disabled.$extra.">\n";
            if (!isset($item['items']) && isset($item['values'])) $item['items'] = $item['values'];
            for($i = 0; $i< count($item['items']); $i++) {
              if (isset($item['values'])) $value = $item['values'][$i]; else $value = $i;
              $body .= '<option value="'.$value.'" '.(count($values) && in_array($value, $values[$item['name']]) ? 'selected="selected"' : '').">".$item['items'][$i]."</option>\n";
            }
            $body .= '</select>'.$comment."</td></tr>\n";
          break;
          case 'checkbox': 
            if (empty($item['name'])) ErrorMessage(sprintf(errFormFieldHasNoName, $item['type'], $form['name']));
            $body .= '<tr><td>&nbsp;</td><td><input type="checkbox" name="'.$item['name'].'" value="'.($value ? $value : true).'" '.($value ? 'checked="checked"' : '').$disabled.$extra.' style="background-color: transparent; border-style: none; margin:0px;" /><span style="vertical-align: baseline"> '.$label."</span></td></tr>\n"; 
          break;
          case 'memo': 
            if (empty($item['name'])) ErrorMessage(sprintf(errFormFieldHasNoName, $item['type'], $form['name']));
            $body .= '<tr><td colspan="2">'.(empty($label)?'':'<span class="formLabel">'.$label.'</span><br />').'<textarea name="'.$item['name'].'" cols="40" rows="'.(empty($item['height'])?'1':$item['height']).'" '.$width.$disabled.$extra.' >'.EncodeHTML($value)."</textarea></td></tr>\n"; 
          break;
          case 'file': 
            if (empty($item['name'])) ErrorMessage(sprintf(errFormFieldHasNoName, $item['type'], $form['name']));
            $body .= '<tr><td class="formLabel">'.$label.'</td><td><input type="file" name="'.$item['name'].'" size="'.$item['width'].'"'.$disabled." />".$comment."</td></tr>\n";
            $file = true;
          break;
          default: ErrorMessage(sprintf(errFormUnknownType, $item['type'], $form['name']));
        }
      }
    }
    $this->scripts .= "
      function ".$form['name']."Submit()
      {
        var result = true;
        var form = document.forms.namedItem('".$form['name']."');
        ".(empty($validator)?'':$validator)."
        if (result) {
          var controls = form.elements;
          var count = controls.length;
          for (var i=0; i < count; i++) if (controls[i].type == 'checkbox') {
            var control = document.createElement('input');
            control.type = 'hidden';
            control.name = controls[i].name;
            control.value = controls[i].checked?controls[i].value:0;
            controls[i].name = '';
            form.appendChild(control);
          }
        }
        return result;
      }
    ";
    #if (!empty($validator)) $this->scripts .= "function ".$form['name']."Submit(strForm)\n{\nvar result = true;\n".$validator.";\nreturn result;\n}\n\n";
    $result .=
      "<div style=\"width: ".$form['width']."\" class=\"form\">\n".
      "<form ".(empty($form['name'])?'':'id="'.$form['name'].'" ')."action=\"".(empty($form['action'])?$request['path'].execScript:$form['action'])."\" method=\"post\"".(empty($validator)?'':' onsubmit="return '.$form['name'].'Submit();"').($file?' enctype="multipart/form-data"':'').">\n".
      "<div class=\"hidden\"><input type=\"hidden\" name=\"submitURL\" value=\"".$this->url()."\" />".
      $hidden."</div>\n".
      "<table>\n".
      (empty($form['caption'])?'':"<tr><th colspan=\"2\">".$form['caption']."</th></tr>\n").
      "<colgroup><col width=\"0*\" /><col width=\"100%\" /></colgroup>\n".
      $body.
      "<tr><td colspan=\"2\" class=\"buttons\"><br />".
      (in_array('ok', $form['buttons'])?'<input type="submit" class="button" value="OK" /> ':'').
      (array_key_exists('ok', $form['buttons'])?'<input type="submit" class="button" value="'.$form['buttons']['ok'].'" /> ':'').
      (in_array('reset', $form['buttons'])?'<input type="reset" class="button" value="'.strReset.'" /> ':'').
      (array_key_exists('reset', $form['buttons'])?'<input type="reset" class="button" value="'.$form['buttons']['reset'].'" /> ':'').
      (in_array('cancel', $form['buttons'])?'<input type="button" class="button" value="'.strCancel.'" onclick="javascript:history.back();" />':'').
      (array_key_exists('cancel', $form['buttons'])?'<input type="button" class="button" value="'.$form['buttons']['cancel'].'" onclick="javascript:history.back();" />':'').
      "</td></tr>\n".
      "</table>\n</form></div>\n";
    return $result;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function buttonAddItem($caption = '', $value = '')
  {
  global $request;
    return '<form class="contentButton" action="'.$request['url'].execScript.'" method="get"><div><input type="hidden" name="action" value="'.(empty($value)?'add':$value).'"><input type="submit" value="'.(empty($caption) ? strAdd : $caption).'" class="contentButton" /></div></form>';
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function buttonBack($caption = '', $url='')
  {
  global $request;
    return '<form class="contentButton" action="" method="get"><div><input type="button" value="'.(empty($caption) ? strReturn : $caption).'" class="contentButton" onclick="'.(empty($url)?'javascript:history.back();':"window.location='".$url."'").'" /></div></form>';
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function button($caption, $url, $name='', $value='')
  {
  global $request;

    $result = '<form class="contentButton" action="'.$url.'" method="get"><div>';
    if (!empty($name)) $result .= '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
    $result .= '<input type="submit" value="'.$caption.'" class="contentButton" onclick="window.location=\''.$url.'\'" /></div></form>';
    return $result;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
}
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#

$page = new TClientUI;
$page->init();
$page->render();
?>
