<?php



class TaggableBehaviour extends CActiveRecordBehavior {
    
    public $tagModel = null;
               
    public $tagTableTitle = 'title';    
    
    public $tagRelationTable = null;    
    
    public $tagRelationTableTagFk = null;    
    
    public $tagRelationTableModelFk = null;
    
    public $tagsSeparator = ',';
    
    protected $tagsList;
    
    protected $originalTagsList;
    
    protected $blankTagModel = null;
    
    protected $tagsAreLoaded = false;
    
    
    public function __construct() {
        $this->originalTagsList = new CMap();
        $this->tagsList = new CMap();
    }
    
    
    public function add() {
        $tagsList = $this->getTagsList( func_get_args() );
        
        $this->tagsList->mergeWith( $tagsList );        
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
        $result = new CMap();
        
        foreach ( $methodArguments as $tagList ) {
                        
            $this->normalizeTagList( $tagList );
            
            foreach ( $tagList as $tag ) {
                
                $tagTitle = $this->prepareTagTitle( $tag );
                
                $result[$tagTitle] = $this->prepareTagObject( $tag, $tagTitle );                
            }            
        }
        
        return $result;
    }
   
    
    protected function loadTags( $additionalCriteria = null ) {
        
        if ( !$this->tagsAreLoaded ) {
            
            /* @var $tagModel CActiveRecord */
            $tagModel = $this->getTagModel();

            $criteria = $this->prepareFindTagsCriteria( $additionalCriteria );

            $tagsList = $tagModel->model()->findAll( $criteria );
            
            $tagTableTitle = $this->tagTableTitle;
            
            foreach ( $tagsList as $tag ) {
                $this->originalTagsList[$tag->$tagTableTitle] = $tag;                
            }  
            
            $this->tagsList->mergeWith( $this->originalTagsList );
            
            $this->tagsAreLoaded = true;
        }
        
        return $this->tagsList;
    }
    
    
    private function normalizeTagList( &$tagList ) {
        
        if ( !is_array( $tagList ) ) {

            if ( is_string( $tagList ) ) {
                $tagList = explode( $this->tagsSeparator, $tagList );                    

            } else {
                $tagList = Array( $tagList );                    
            }
        }        
    }    
    
    
    protected function prepareFindTagsCriteria( $additionalCriteria ) {
        
        $relationTable = $this->getRelationTable();
        $relationTagFk = $this->getRelationTagFk();
        $relationModelFk = $this->getRelationModelFk();

        $result = new CDbCriteria(
            Array(

                'join' => 'INNER JOIN '.$relationTable
                    .' ON '.$relationTagFk.' = '.$this->getTagPk(),

                'condition' => $relationModelFk.' = :modelId',

                'params' => Array(
                    'modelId' => $this->owner->primaryKey
                )
            )
        );

        if ( !empty( $additionalCriteria )) {
            $result->mergeWith( $additionalCriteria );
        }
        
        return $result;       
    }
    
    
    protected function prepareTagObject( $tag, $tagTitle ) {
        
        /* @var $tagModel CActiveRecord */
        $tagModel = $this->getTagModel();
        $tagModelClass = get_class( $tagModel );

        if ( isset( $this->tagsList[$tagTitle] ) ) {
            $result = $this->tagsList[$tagTitle];

        } else {

            if ( is_object( $tag ) && $tag instanceof $tagModelClass ) {
                $result = $tag;

            } else {
                $result = Yii::createComponent( Array(
                    'class' => $this->tagModel,
                    $this->tagTableTitle => $tagTitle
                ) );
            }                    
        }                                
        
        return $result;
    }
    
    
    protected function prepareTagTitle( $tag ) {    
        
        /* @var $tagModel CActiveRecord */        
        $tagModel = $this->getTagModel();
        $tagModelClass = get_class( $tagModel );
        
        if ( $tag instanceof $tagModelClass ) {
            $tagTableTitle = $this->tagTableTitle;

            $tagTitle = $tag->$tagTableTitle;                        

        } elseif ( is_object( $tag ) && !method_exists( $tag, '__toString' ) ) {                        
            throw new Exception( 
                'It is unable to typecast to String object of class '
                .get_class( $tag ) 
            );                                                                    
            
        } else {
            $tagTitle = (string) $tag;                                                                                                
        }

        $result = trim( strip_tags( $tagTitle ) );       
        
        return $result;
    }
   
    
    public function remove() {
        $tagsList = $this->getTagsList( func_get_args() );        
                    
        foreach ( array_keys( $tagsList->toArray() ) as $key ) {
            $this->tagsList->remove( $key );
        }        
    }    
    

    public function reset() {
        $this->tagsList->clear();
    }
    
    
    public function set() {
        $this->tagsList = $this->getTagsList( func_get_args() );        
    }
        
}

?>