<?php
namespace GFL\ReadDocx;
use ZipArchive;

class ReadDocx
{
  public $path_file;
  public $result;
  public function loadFile($file = [])
  {
      if (isset($file)) {
          $errors = array();
          $zip = new ZipArchive;
          $extension = explode('.', $file["name"]);

          if (end($extension) !== 'docx') {
              $errors[] = 'Error: Wrong format';
          }
          
          if ($zip->open($file['tmp_name']) === false) {
              $errors[] = 'Failed to open file';
          }

          if (empty($errors)) {
              $zip->extractTo('doc/' . $file["name"] . '/tmp_doc');
              $zip->close();
              $this->path_file = 'doc/' . $file["name"] . '/tmp_doc/word/document.xml';
          }
      }
      return $this;
  }

  public function getIndex()
  {
      $rows = [];
      $row = [];
      $xml = simplexml_load_file($this->path_file, null, 0, 'w', true) or die("Error: Cannot create object");
      $body = ($xml->body[0]);
      foreach ($body->sdt as $key => $std) {
          foreach ($std->sdtContent[0]->p as $key => $value) {
              if ($value->hyperlink[0]) {
                  $row['level'] = $this->getLevel($this->xml_attribute($value->pPr[0]->pStyle, 'val'));
                  $row['id'] = uniqid();
                  $row['anchor'] = $this->xml_attribute($value->hyperlink[0], 'anchor');
                  $row['header'] = ' ';
                  foreach ($value->hyperlink[0]->r as $k => $v) {
                      if ($this->xml_attribute($v->rPr[0]->rStyle, 'val')) {
                          $row['header'] .= $v->t;
                      }
                  }
                  if ($row['level'] == 1) {
                      $row['parent_id'] = 0;
                  } else {
                      $row['parent_id'] = $this->findIndex($rows, $row['level']);
                  }
                  $rows[] = $row;
              }
          }
      }
      $this->result = $this->Recursive($rows);
      return $this;
  }

  public function toJson()
  {
      return json_encode($this->result,JSON_PRETTY_PRINT);
  }

  public function toHtml()
  {
      return $this->recursiveHtml($this->result);
  }

  public function recursiveHtml($arr)
  {
      $str = "<ul>";
      foreach ($arr as $key => $val) {
          $str .= "<li>".$val['header']."</li>";
          if (isset($val['children'])) {
              $str .= $this->recursiveHtml($val['children']);
          }
      }
      $str .= "</ul>";

      return $str;
  }

  public function get()
  {
      return $this->result;
  }

  public function findIndex($array = [], $level)
  {
      if ($level == 1) {
          return false;
      } else {
          foreach (array_reverse($array) as $key => $value) {
              if ($value['level'] == $level - 1) {
                  return $value['id'];
              }
          }
      }
      return false;
  }

  public function Recursive($array = [], $parent_id = 0)
  {
      $out = [];
      foreach ($array as $key => $value) {
          if ($value['parent_id'] == $parent_id) {
              $children = $this->Recursive($array, $value['id']);
              if ($children) {
                  $value['children'] = $children;
              }
              $out[] = $value;
          }
      }
      return $out;
  }

  public function xml_attribute($object, $attribute)
  {
      if (isset($object[$attribute])) {
          return (string) $object[$attribute];
      }

      return false;
  }

  // input : 'TOC5'
  // output : '5'
  public function getLevel($pStyle)
  {
      return substr($pStyle, 3);
  }

  public function getContent()
  {
      $xml = simplexml_load_file($this->path_file, null, 0, 'w', true) or die("Error: Cannot create object");
      $body = ($xml->body[0]);
      foreach ($body->p as $key => $value) {
          if($value->bookmarkStart) {
              foreach ($value->bookmarkStart as $key => $bookmark) {
                  $name = $this->xml_attribute($bookmark  ,'name');
              }
              echo '<br>'.$name.'<br>';
          }
          
          foreach ($value->r as $k => $r) {
              if(!$value->bookmarkStart){
                  echo $r->t;
              }
          }
      }
  }
}
