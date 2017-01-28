<?php
namespace ChurchCRM\dto;

use ChurchCRM\Config;

class ConfigItem
{
  private static $id, $name, $value, $type, $default, $tooltip, $data, $dbConfigItem;
  public function __construct($id, $name, $type, $default, $tooltip, $data) {
    $this->id = $id;
    $this->name = $name;
    $this->type = $type;
    $this->default = $default; 
    $this->tooltip = $tooltip;
    $this->data = $data;
  }
  
  public function getId()
  {
    return $this->id;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function setDBConfigObject($dbConfigItem)
  {
    $this->dbConfigItem = $dbConfigItem;
    $this->value = $dbConfigItem->getValue();
  }
  
  public function getDBConfigObject()
  {
    return $this->dbConfigItem ;
  }
  
  public function getValue()
  {
    if ( isset( $this->value ) )
    {
      return $this->value;
    }
    else
    {
      return $this->default;
    }
  }
  
  public function setValue($value)
  {
    if ( ! isset ($this->dbConfigItem) )
    {
      $this->dbConfigItem = new Config();
      $this->dbConfigItem->setId($this->getId());
      $this->dbConfigItem->setName($this->getName());
    }
    $this->dbConfigItem->setValue($value);
    $this->dbConfigItem->save();
    $this->value=$value;
  }
  
  public function getDefault()
  {
    return $this->default;
  }
          
  
  public function getType()
  {
    return $this->type;
  }
  
  public function getTooltip()
  {
    return $this->tooltip;
  }
  
  public function getSection()
  {
    return $this->section;
  }
  
  public function getCategory()
  {
    return $this->category;
  }
  
  public function getData()
  {
    return $this->data;
  }

}