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
        $tagsList = $this->getTagsList( func_get_args() );
    }
    
    
    public function remove() {
        $tagsList = $this->getTagsList( func_get_args() );        
    }    
    
    
    protected function getTagsList( $methodArguments ) {
        $result = Array();
        
        foreach ( $methodArguments as $tagList ) {
                        
            if ( !is_array( $tagList ) ) {
                
                if ( is_string( $tagList ) ) {
                    $tagList = explode( ',', $tagList );                    
                    
                } else {
                    $tagList = Array( $tagList );                    
                }
            }
            
            foreach ( $tagList as $tag ) {
                
                if ( is_object( $tag ) && !method_exists( $tag, '__toString' ) ) {
                    throw new Exception( 'It is unable to typecast to String object of class '.  get_class( $tag ) );                    
                }

                $tag = (string) $tag;
                                
                $result[$tag] = trim( strip_tags( $tag ) );
            }            
        }
        
        return $result;
    }
        
}

?>