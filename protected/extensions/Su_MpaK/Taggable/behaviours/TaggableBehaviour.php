<?php



/**
 * Behaviour for CActiveRecord that allows to attach tags to a model.
 * 
 * @author Denis Poltoratsky <356187@gmail.com>
 * 
 * @link https://github.com/dpoltoratsky/Taggable
 * 
 * @license http://opensource.org/licenses/bsd-license.php The BSD 2-Clause License
 * 
 * @package ext.Su_MpaK.Taggable.behaviours
 * 
 * @version 1.11.01.01
 */
class TaggableBehaviour extends CActiveRecordBehavior {
   
    /**
     * Tag model path alias.
     * 
     * Will be passed as 'class' attribute value to Yii::createComponent().
     * 
     * @var string 
     * 
     * @see YiiBase::createComponent()
     */
    public $tagModel = null;
               
    /**
     * The field name which contains tag title.
     * 
     * Will be passed to CActiveRecord::getAttribute().
     * 
     * @var string
     * 
     * @see CActiveRecord::getAttribute()
     */
    public $tagTableTitle = 'title';    
    
    /**
     * The name of relation table.
     * 
     * By default will be '{modelTableName}_{tagTableName}'.
     * 
     * @var string
     */
    public $tagRelationTable = null;    
    
    /**
     * The name of attribute in relation table which recalls tag.
     * 
     * By default will be '{tagTableName}Id'.
     * 
     * @var string
     */
    public $tagRelationTableTagFk = null;    
    
    /**
     * The name of attribute in relation table which recalls model.
     * 
     * By default will be '{modelTableName}Id'.
     * 
     * @var string
     */
    public $tagRelationTableModelFk = null;
    
    /**
     * Separator for tags in strings.
     * 
     * @var string
     */
    public $tagsSeparator = ',';
    
    /**
     * The list of attached to model tags.
     * 
     * @var CMap 
     * 
     * @see CMap
     */
    protected $tagsList;
    
    /**
     * Instance of blank (without attributes) tag model for internal usage.
     * 
     * @var CActiveRecord
     * 
     * @see CActiveRecord
     */
    protected $blankTagModel = null;
    
    /**
     * Shows were tags already loaded from DB or not.
     * 
     * @var bool
     */
    protected $tagsAreLoaded = false;
    
    
    /**
     * Initialises tagsList with new CMap.
     * 
     * @see $tagsList
     * @see CMap
     */
    public function __construct() {
        $this->tagsList = new CMap();
    }
    
    
    /**
     * Attaches tags to model.
     * 
     * Can be called with any number of arguments of any type. Only constraint 
     * is that Object arguments should have __toString defined (Not applicable 
     * to instances of tag model).
     * 
     * @return CActiveRecord Model that behaviour is attached to.
     * 
     * @see CActiveRecord
     */
    public function add() {
        $this->loadTags()->mergeWith( $this->getTagsList( func_get_args() ) );
        
        return $this->owner;
    }

    
    /**
     * Removes all attached tags after model has been deleted.
     * 
     * @param CModelEvent $event
     * 
     * @see CModelEvent
     */
    public function afterDelete( $event ) {
        $this->clearAttachedTags();            
        
        parent::afterDelete( $event );
    }
    
    
    /**
     * Saves all attached tags.
     * 
     * @param CModelEvent $event
     * 
     * @see CModelEvent
     */
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
    
    
    /**
     * Clears all tags attachments.
     */
    protected function clearAttachedTags() {
        Yii::app()->db->createCommand()->delete(
            $this->getRelationTable(),
            sprintf( '%s = :modelId', $this->getRelationModelFk() ),
            Array(
                'modelId' => $this->owner->primaryKey
            )
        );        
    }
    
    
    /**
     * Returns all attached to model tags.
     * 
     * @param CDbCriteria $additionalCriteria Additional DB criteria to filter attached tags. Will be passed to CDbCriteria::mergeWith()
     * @return CMap All attached to the model tags.
     * 
     * @see CMap
     * @see CDbCriteria
     * @see CDbCriteria::mergeWith()
     */
    public function get( $additionalCriteria = null ) {
        return $this->loadTags( $additionalCriteria );        
    }

    
    /**
     * Returns model table primary key field name.
     * 
     * @param bool $full If true - field name will be prepended with model table alias.
     * @return string Model table primary key field name.
     */
    protected function getModelPk( $full = true ) {
        $result = $this->owner->tableSchema->primaryKey;
        
        if ( $full ) {
            $result = $this->owner->tableAlias.'.'.$result;
        }
        
        return $result; 
    }
    
    
    /**
     * Returns relation table field name that recalls model.
     * 
     * @param bool $full If true - field name will be prepended with relational table name.
     * @return string Relation table field name that recalls model.
     */
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
    
    
    /**
     * Returns relation table name.
     * 
     * @return string Relation table name.
     */
    protected function getRelationTable() {
        
        if ( empty( $this->tagRelationTable ) ) {           
            $this->tagRelationTable = 
                $this->owner->tableName()
                .'_'
                .$this->getTagModel()->tableName();
        }
        
        return $this->tagRelationTable;
    }

    
    /**
     * Returns relation table filed name that recalls tag.
     * 
     * @param bool $full If true - field name will be prepended with relation table name.
     * @return string Relation table filed name that recalls tag.
     */
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


    /**
     * Returns blank (without attributes set) tag model.
     * 
     * @return CActiveRecord Blank (without attributes set) tag model.
     * 
     * @see CActiveRecord
     */
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
    
    
    /**
     * Returns tag table primary key field name.
     * 
     * @param bool $full If true - field name will be prepended with tag table name or alias.
     * @param bool $alias If true - field will be prepended with table alias, other way - with table name.
     * @return string Tag table primary key field name.
     */
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
    
    
    /**
     * Returns tag table title field name.
     * 
     * @param bool $full If true - field name will be prepended with tag table name or alias.
     * @param bool $alias If true - field will be prepended with table alias, other way - with table name.
     * @return string Tag table title field name.
     */
    protected function getTagTitle( 
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
    
    
    /**
     * Parses array of arguments were passed to one of interface methods and 
     * creates a has map (CMap) of corresponding tag objects.
     * 
     * @param Array $methodArguments The list of input parameters were passed to interface method.
     * @return CMap List of tag object corresponding to input parameters.
     * 
     * @see CMap
     */
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
    
    
    /**
     * Checks whether or not specified tags are attached to the model.
     * 
     * Can be called with any number of arguments of any type. Only constraint 
     * is that Object arguments should have __toString defined (Not applicable 
     * to instances of tag model).
     * 
     * @return boolean True if ALL specified tags are attached to the model.
     */
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
    
        
    /**
     * Loads all attached to the model tags from the DB.
     * 
     * @param CDbCriteria $additionalCriteria
     * @return CMap All attached to the model tags.
     * 
     * @see CMap
     * @see CDbCriteria
     */
    protected function loadTags( $additionalCriteria = null ) {
        
        if ( !$this->tagsAreLoaded ) {
            
            /* @var $tagModel CActiveRecord */
            $tagModel = $this->getTagModel();

            $criteria = $this->prepareFindTagsCriteria( $additionalCriteria );

            $tagsList = $tagModel->model()->findAll( $criteria );
            
            $tagTableTitle = $this->getTagTitle( false );            
            
            /* @var $tag CActiveRecord */
            foreach ( $tagsList as $tag ) {
                $tagTitle = $tag->getAttribute( $tagTableTitle );
                
                $this->tagsList[$tagTitle] = $tag;                
            }  
            
            $this->tagsAreLoaded = true;
        }
        
        return $this->tagsList;
    }
    
    
    /**
     * Makes sure that a list of tags is an array.
     * 
     * @param Array $tagList
     */
    private function normalizeTagList( &$tagList ) {
        
        if ( !is_array( $tagList ) ) {

            if ( is_string( $tagList ) ) {
                $tagList = explode( $this->tagsSeparator, $tagList );                    

            } else {
                $tagList = Array( $tagList );                    
            }
        }        
    }    
    
    
    /**
     * Prepares search criteria to find tags attached to the model.
     * 
     * @param CDbCriteria $additionalCriteria Additional criteria to filet attached tags.
     * @return CDbCriteria Criteria to find tags attached to the model.
     * 
     * @see CDbCriteria
     */
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
    
    
    /**
     * Makes sure that tag object will be instance of tag model.
     * 
     * @param mixed $tag Initial value of tag
     * @param string $tagTitle Tag title
     * @return CActiveRecord Tag object
     * 
     * @see CActiveRecord
     */
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
    
    
    /**
     * Prepares string tag title.
     * 
     * @param mixed $tag initial tag value
     * @return string Tag title
     * 
     * @throws Exception
     */
    protected function prepareTagTitle( $tag ) {    
        
        /* @var $tagModel CActiveRecord */        
        $tagModel = $this->getTagModel();
        $tagModelClass = get_class( $tagModel );
        
        if ( $tag instanceof $tagModelClass ) {
            $tagTitle = $tag->getAttribute( $this->getTagTitle( false ) );                        

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
   
    
    /**
     * Detaches specified tags from the model.
     * 
     * Can be called with any number of arguments of any type. Only constraint 
     * is that Object arguments should have __toString defined (Not applicable 
     * to instances of tag model).
     * 
     * @return CActiveRecord Model that behaviour is attached to.
     * 
     * @see CActiveRecord
     */
    public function remove() {
        $this->loadTags();

        $tagsList = $this->getTagsList( func_get_args() );        
            
        foreach ( array_keys( $tagsList->toArray() ) as $tagTitle ) {
            $this->tagsList->remove( $tagTitle );
        }     
        
        return $this->owner;        
    }    
    

    /**
     * Detaches all tags from the model.
     * 
     * @return CActiveRecord Model that behaviour is attached to.
     * 
     * @see CActiveRecord
     */
    public function reset() {
        $this->tagsAreLoaded = true;
        
        $this->tagsList->clear();
        
        return $this->owner;        
    }
    
    
    /**
     * Attaches to the model specified set of tags that will replace all 
     * previous ones.
     * 
     * Can be called with any number of arguments of any type. Only constraint 
     * is that Object arguments should have __toString defined (Not applicable 
     * to instances of tag model).
     * 
     * Model will be selected if it has AT LEAST ONE of the specified tags attached.
     * 
     * @return CActiveRecord Model that behaviour is attached to.
     * 
     * @see CActiveRecord
     */
    public function set() {
        $this->tagsAreLoaded = true;
        
        $this->tagsList = $this->getTagsList( func_get_args() );        
        
        return $this->owner;        
    }
    

    /**
     * Modifies the model DB criteria in order to find models with any of 
     * specidied tags attached.
     * 
     * Can be called with any number of arguments of any type. Only constraint 
     * is that Object arguments should have __toString defined (Not applicable 
     * to instances of tag model).
     * 
     * @return CActiveRecord Model that behaviour is attached to.
     * 
     * @see CActiveRecord
     */
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
        
    
    /**
     * Allows all attached to the model tags to be printed imploded by 
     * separator.
     * 
     * @return string Imploded by separator tags attached to the model.
     * 
     * @see $tagsSeparator
     */
    public function __toString() {
        $this->loadTags();
        
        return implode( 
            $this->tagsSeparator, 
            array_keys( $this->tagsList->toArray() ) 
        );
    }
}

?>