<?php



class TaggableBehaviour extends CActiveRecordBehavior {
    
    public $tagModel = null;
               
    public $tagTableTitle = 'title';    
    
    public $tagRelationTable = null;    
    
    public $tagRelationTableTagFk = null;    
    
    public $tagRelationTableModelFk = null;
    
    protected $tagsList = Array();
    
    protected $originalTagsList = Array();
    
    protected $blankTagModel = null;
    
    protected $tagsAreLoaded = false;
    
    
    public function add() {
        $tagsList = $this->getTagsList( func_get_args() );
        
        $this->tagsList = array_unique( array_merge( $this->tagsList, $tagsList ) );        
    }

    
    public function afterDelete( $event ) {
        
        parent::afterDelete( $event );
    }
    
    
    public function afterSave( $event ) {
                
        parent::beforeSave($event);
    }
    
    
    public function get() {
        return $this->loadTags();        
    }
    
    
    protected function getRelationModelFk( $full = true ) {
        $result = '';
        
        if ( empty( $this->tagRelationTableModelFk ) ) {
            $this->tagRelationTableModelFk = $this->owner->tableName().'Id';
        }
        
        if ( $full ) {
            $result = $this->getRelationTable().'.';
        }
        
        $result .= $this->tagRelationTableModelFk;
        
        return $result;
    }
    
        
    protected function getRelationTable() {
        
        if ( empty( $this->tagRelationTable ) ) {           
            $this->tagRelationTable = 
                $this->owner->tableName()
                .'_'
                .$this->getTagModel()->tableName();
        }
        
        return $this->tagRelationTable;
    }

    
    protected function getRelationTagFk( $full = true ) {
        $result = '';
        
        if ( empty( $this->tagRelationTableTagFk ) ) {
            
            /* @var $tagModel CActiveRecord */
            $tagModel = $this->getTagModel();
            
            $this->tagRelationTableTagFk = $tagModel->tableName().'Id';
        }
        
        if ( $full ) {                        
            $result = $this->getRelationTable().'.';
        }
        
        $result .= $this->tagRelationTableTagFk;
        
        return $result;
    }

    
    protected function getTagModel() {
        
        if ( empty( $this->blankTagModel ) ) {            
            $this->blankTagModel = Yii::createComponent(
                Array(
                    'class' => $this->tagModel
                )
            );
        }
        
        return $this->blankTagModel;
    }
    
    
    protected function getTagPk( $full = true ) {
        
        /* @var $tagModel CActiveRecord */
        $tagModel = $this->getTagModel();
        
        $result = $tagModel->tableSchema->primaryKey;
        
        if ( $full ) {
            $result = $tagModel->tableAlias.'.'.$result;
        }
        
        return $result;
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
                
                if ( 
                    is_object( $tag ) 
                    && !method_exists( $tag, '__toString' ) 
                ) {
                    
                    throw new Exception( 
                        'It is unable to typecast to String object of class '
                        .get_class( $tag ) 
                    );                    
                }

                $tag = (string) $tag;
                                
                $result[$tag] = trim( strip_tags( $tag ) );
            }            
        }
        
        return $result;
    }
    
    
    protected function loadTags() {
        
        if ( !$this->tagsAreLoaded ) {
            
            /* @var $tagModel CActiveRecord */
            $tagModel = $this->getTagModel();

            $relationTable = $this->getRelationTable();
            $relationTagFk = $this->getRelationTagFk();
            $relationModelFk = $this->getRelationModelFk();

            $criteria = new CDbCriteria(
                Array(

                    'join' => 'INNER JOIN '.$relationTable
                        .' ON '.$relationTagFk.' = '.$this->getTagPk(),

                    'condition' => $relationModelFk.' = :modelId',

                    'params' => Array(
                        'modelId' => $this->owner->primaryKey
                    )
                )
            );

            $tagsList = $tagModel->model()->findAll( $criteria );

            $tagTableTitle = $this->tagTableTitle;
            
            foreach ( $tagsList as $tag ) {
                $this->originalTagsList[$tag->$tagTableTitle] = $tag;                
            }  
            
            $this->tagsList = array_merge( 
                $this->tagsList, 
                $this->originalTagsList 
            );
            
            $this->tagsAreLoaded = true;
        }
        
        return $this->tagsList;
    }
    
    
    public function remove() {
        $tagsList = $this->getTagsList( func_get_args() );        
        
        $this->tagsList = array_diff( $this->tagsList, $tagsList );
    }    
    

    public function reset() {
        $this->tagsList = Array();
    }
    
    
    public function set() {
        $tagsList = $this->getTagsList( func_get_args() );        

        $this->tagsList = $tagsList;
    }
        
}

?>