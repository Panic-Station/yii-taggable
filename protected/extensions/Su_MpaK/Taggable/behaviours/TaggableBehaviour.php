<?php



class TaggableBehaviour extends CActiveRecordBehavior {
    
    public $tagModel = null;
               
    public $tagTableTitle = 'title';    
    
    public $tagRelationTable = null;    
    
    public $tagRelationTableTagFk = null;    
    
    public $tagRelationTableModelFk = null;
    
    public $tagsSeparator = ',';
    
    protected $tagsList;
    
    protected $blankTagModel = null;
    
    protected $tagsAreLoaded = false;
    
    
    public function __construct() {
        $this->tagsList = new CMap();
    }
    
    
    public function add() {
        $this->loadTags()->mergeWith( $this->getTagsList( func_get_args() ) );
        
        return $this->owner;
    }

    
    public function afterDelete( $event ) {
        $this->clearAttachedTags();            
        
        parent::afterDelete( $event );
    }
    
    
    public function afterSave( $event ) {
        $this->loadTags();
        
        if ( !$this->owner->isNewRecord ) {
            $this->clearAttachedTags();            
        }
        
        /* @var $tag CActiveRecord */
        foreach ( $this->tagsList as $tag ) {            
            
            if ( $tag->isNewRecord ) {
                $tag->save();
            }
                                    
            Yii::app()->db->createCommand()->insert(
                $this->getRelationTable(), 
                Array(
                    
                    $this->getRelationModelFk( false ) => 
                        $this->owner->primaryKey,
                    
                    $this->getRelationTagFk( false ) => $tag->primaryKey
                )
            );
        }
        
        parent::beforeSave($event);
    }
    
    
    protected function clearAttachedTags() {
        Yii::app()->db->createCommand()->delete(
            $this->getRelationTable(),
            sprintf( '%s = :modelId', $this->getRelationModelFk() ),
            Array(
                'modelId' => $this->owner->primaryKey
            )
        );        
    }
    
    
    public function get( $additionalCriteria = null ) {
        return $this->loadTags( $additionalCriteria );        
    }

    
    protected function getModelPk( $full = true ) {
        $result = $this->owner->tableSchema->primaryKey;
        
        if ( $full ) {
            $result = $this->owner->tableAlias.'.'.$result;
        }
        
        return $result;
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
    
    
    protected function getTagPk( 
        $full = true, 
        $alias = true 
    ) {
        
        /* @var $tagModel CActiveRecord */
        $tagModel = $this->getTagModel();
        
        $result = $tagModel->tableSchema->primaryKey;
        
        if ( $full ) {
            
            if ( $alias ) {
                $prefix = $tagModel->tableAlias;
                
            } else {
                $prefix = $tagModel->tableName();
            }
            
            $result = $prefix.'.'.$result;
        }
        
        return $result;
    }    
    
    
    public function getTagTitle( 
        $full = true, 
        $alias = true 
    ) {
        
        $result = $this->tagTableTitle;
        
        if ( $full ) {
            /* @var $tagModel CActiveRecord */
            $tagModel = $this->getTagModel();
            
            if ( $alias ) {
                $prefix = $tagModel->tableAlias;
                
            } else {
                $prefix = $tagModel->tableName();
            }
            
            $result = $prefix.'.'.$result;
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
    
    
    public function has() {
        $this->loadTags();
        
        $tagsList = $this->getTagsList( func_get_args() );
        
        $result = true;
        
        foreach ( array_keys( $tagsList->toArray() ) as $tagTitle ) {
            
            if ( !$this->tagsList->contains( $tagTitle ) ) {
                $result = false;
                break;
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
            
            $tagTableTitle = $this->getTagTitle( false );
            
            foreach ( $tagsList as $tag ) {
                $this->tagsList[$tag->$tagTableTitle] = $tag;                
            }  
            
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
        $result = new CDbCriteria(
            Array(

                'join' => sprintf( 
                    'INNER JOIN %s ON %s = %s ', 
                    $this->getRelationTable(), 
                    $this->getRelationTagFk(), 
                    $this->getTagPk()
                ),

                'condition' => sprintf( 
                    '%s = :modelId', 
                    $this->getRelationModelFk() 
                ),

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
    
    
    protected function prepareTagObject( 
        $tag, 
        $tagTitle 
    ) {
        
        /* @var $tagModel CActiveRecord */
        $tagModel = $this->getTagModel();
        $tagModelClass = get_class( $tagModel );

        if ( isset( $this->tagsList[$tagTitle] ) ) {
            $result = $this->tagsList[$tagTitle];

        } else {

            if ( is_object( $tag ) && $tag instanceof $tagModelClass ) {
                $result = $tag;

            } else {                
                $existingTag = $tagModel->model()->find(
                    sprintf( '%s = :title', $this->getTagTitle() ),
                    Array(
                        'title' => $tagTitle
                    )
                );
                
                if ( $existingTag ) {
                    $result = $existingTag;
                    
                } else {
                    $result = Yii::createComponent( Array(
                        'class' => $this->tagModel,
                        $this->getTagTitle( false ) => $tagTitle
                    ) );                    
                }
            }                    
        }                                
        
        return $result;
    }
    
    
    protected function prepareTagTitle( $tag ) {    
        
        /* @var $tagModel CActiveRecord */        
        $tagModel = $this->getTagModel();
        $tagModelClass = get_class( $tagModel );
        
        if ( $tag instanceof $tagModelClass ) {
            $tagTableTitle = $this->getTagTitle( false );

            $tagTitle = $tag->$tagTableTitle;                        

        } elseif ( is_object( $tag ) && !method_exists( $tag, '__toString' ) ) {             
            throw new Exception( 
                sprintf( 
                    'It is unable to typecast to String object of class %s',
                    get_class( $tag )  
                )
            );                                                                    
            
        } else {
            $tagTitle = (string) $tag;                                                                                                
        }

        $result = trim( strip_tags( $tagTitle ) );       
        
        return $result;
    }
   
    
    public function remove() {
        $this->loadTags();

        $tagsList = $this->getTagsList( func_get_args() );        
            
        foreach ( array_keys( $tagsList->toArray() ) as $tagTitle ) {
            $this->tagsList->remove( $tagTitle );
        }     
        
        return $this->owner;        
    }    
    

    public function reset() {
        $this->tagsList->clear();
        
        return $this->owner;        
    }
    
    
    public function set() {
        $this->tagsList = $this->getTagsList( func_get_args() );        
        
        return $this->owner;        
    }
    
        
    public function taggedWith() {
        $tagsList = $this->getTagsList( func_get_args() );
       
        /* @var $tagModel CActiveRecord */
        $tagModel = $this->getTagModel();       
       
        $criteria = new CDbCriteria( Array(
            
            'join' => sprintf(
                'INNER JOIN %s ON %s = %s INNER JOIN %s ON %s = %s',
                $this->getRelationTable(),
                $this->getRelationModelFk(),
                $this->getModelPk(),
                $tagModel->tableName(),
                $this->getRelationTagFk(),
                $this->getTagPk( true, false )
            ),
            
            'distinct' => true,
            
        ) );
                
        $criteria->addInCondition( 
            $this->getTagTitle( true, false ), 
            array_keys( $tagsList->toArray() )
        );
        
        $this->owner->getDbCriteria()->mergeWith( $criteria );
        
        return $this->owner;
    }
        
    
    public function __toString() {
        $this->loadTags();
        
        return implode( 
            $this->tagsSeparator, 
            array_keys( $this->tagsList->toArray() ) 
        );
    }
}

?>