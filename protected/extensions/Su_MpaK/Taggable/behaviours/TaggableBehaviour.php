<?php



class TaggableBehaviour extends CActiveRecordBehavior {
    
    
    public $tagTable = 'tag';
        
    public $tagTablePk = 'id';
       
    public $tagTableTitle = 'title';    
    
    public $tagTableQuantity = 'quantity';    
    
    public $tagRelationTable = null;    
    
    public $tagRelationTableTagFk = 'tagId';    
    
    public $tagRelationTableModelFk = null;
    
    
    public function add() {
        
    }
    
    
    public function remove() {
        
    }    
    
}

?>