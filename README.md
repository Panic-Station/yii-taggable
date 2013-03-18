Taggable
========

Behaviour for CActiveRecord that allows to attach tags to a model.

**License:** [The BSD 2-Clause License](http://opensource.org/licenses/bsd-license.php)

**Package:** ext.Su_MpaK.Taggable.behaviours

**Inheritance:** class Taggable >> [CActiveRecordBehavior](http://www.yiiframework.com/doc/api/1.1/CActiveRecordBehavior) >> [CModelBehavior](http://www.yiiframework.com/doc/api/1.1/CModelBehavior) >> [CBehavior](http://www.yiiframework.com/doc/api/1.1/CBehavior) >> [CComponent](http://www.yiiframework.com/doc/api/1.1/CComponent)

## Table of contents
[Public properties](#public-properties)
- [tagModel](#tagmodel)
- [tagTableTitle](#tagtabletitle)
- [tagRelationTable](#tagrelationtable)
- [tagRelationTableTagFk](#tagrelationtabletagfk)
- [tagRelationTableModelFk](#tagrelationtablemodelfk)
- [tagsSeparator](#tagsseparator)

[Configuration examples](#configuration-examples)
- [Minimal configuration](#minimal-configuration)
- [Complete configuration](#complete-configuration)
- [Configuration for different types of tags](#configuration-for-different-types-of-tags)

[Public methods](#public-methods)
- [add](#add)
- [get](#get)
- [has](#has)
- [remove](#remove)
- [reset](#reset)
- [set](#set)
- [taggedWith](#taggedwith)
- [__toString](#__toString)

## Public properties

### tagModel

```php
public string $tagModel
```

Tag model path alias.

Will be passed as 'class' attribute value to [Yii::createComponent()](http://www.yiiframework.com/doc/api/1.1/YiiBase#createComponent-detail).

### tagTableTitle

```php
public string $tagTableTitle = 'title'
```

The field name which contains tag title.

Will be passed to [CActiveRecord::getAttribute()](http://www.yiiframework.com/doc/api/1.1/CActiveRecord#getAttribute-detail).

### tagRelationTable

```php
public string $tagRelationTable
```

The name of relation table.

By default will be '{modelTableName}_{tagTableName}'.

### tagRelationTableTagFk

```php
public string $tagRelationTableTagFk
```

The name of attribute in relation table which recalls tag.

By default will be '{tagTableName}Id'.

### tagRelationTableModelFk

```php
public string $tagRelationTableModelFk
```

The name of attribute in relation table which recalls model.

By default will be '{modelTableName}Id'.

### tagsSeparator

```php
public string $tagsSeparator = ','
```

Separator for tags in strings.

## Configuration examples

### Minimal configuration

```php
class User extends CActiveRecord {
    ...
 
    public function behaviors() {
        return Array(
            'tags' => Array(
                'class' => 'ext.Su_MpaK.Taggable.behaviours.TaggableBehaviour',
 
                // Tag model path alias.
                'tagModel' => 'Tag'
            )
        );
    }
 
    ...
}
```

### Complete configuration

```php
 class User extends CActiveRecord {
    ...
  
    public function behaviors() {
        return Array(
            'tags' => Array(
                'class' => 'ext.Su_MpaK.Taggable.behaviours.TaggableBehaviour',
 
                // Tag model path alias.
                'tagModel' => 'Tag',
 
                // The field name which contains tag title.
                'tagTableTitle' => 'title',
 
                // The name of relation table.
                'tagRelationTable' => 'tbl_user_tag',
                 
                // The name of attribute in relation table which recalls tag.
                'tagRelationTableTagFk' => 'tagId',
 
                // The name of attribute in relation table which recalls model.
                'tagRelationTableModelFk' => 'tbl_userId',
 
                // Separator for tags in strings.
                'tagsSeparator' => ','
            )
        );
    }
  
    ...
}
```

### Configuration for different types of tags

```php
class User extends CActiveRecord {
    ...
  
    public function behaviors() {
        return Array(
            'tags' => Array(
                'class' => 'ext.Su_MpaK.Taggable.behaviours.TaggableBehaviour',
  
                // Tag model path alias.
                'tagModel' => 'Tag'
            ),
 
            'categories' => Array(
                'class' => 'ext.Su_MpaK.Taggable.behaviours.TaggableBehaviour',
  
                // Category tag model path alias.
                'tagModel' => 'Category'
            ),
        );
    }
  
    ...
}
 
...
 
$user->tags->add( 'test, test1', 'test2' );
$user->categories->add( 'cat1', Array( 'cat2, cat3', 'cat4' ) );
 
$user->save();
 
...
```

## Public methods

### add

```php
public CActiveRecord add( $... )
```

Attaches tags to model.

Can be called with any number of arguments of any type. Only constraint is that Object arguments should have __toString defined (Not applicable to instances of tag model).

*Usage example:*

```php
$user->tags->add( 'test', Array( 'admin', 10 ), $user )->save();
 
// This code will attach to the model following tags: 'test', 'admin', '10', 'User_1' ($user->__toString())
```

### get

```php
public CMap get( CDbCriteria $additionalCriteria = null )
```

Returns all attached to the model tags.

*Parameters:*

additionalCriteria - Additional DB criteria to filter attached tags. Will be passed to [CDbCriteria::mergeWith()](http://www.yiiframework.com/doc/api/1.1/CDbCriteria#mergeWith-detail).

*Usage example:*

```php
$tagsList = $user->tags->get(); 
```

### has

```php
public bool has( $... )
```

Checks whether or not specified tags are attached to the model.

Can be called with any number of arguments of any type. Only constraint is that Object arguments should have __toString defined (Not applicable to instances of tag model).

Returns true only if ALL specified tags are attached to the model.

*Usage example:*

```php
// if tags 'test', 'admin' и '10' are attached to the user
if ( $user->tags->has( 'test', Array( 'admin' ), 10 ) ) {
    ...
}
```

### remove

```php
public CActiveRecord remove( $... )
```

Detaches specified tags from the model.
 
Can be called with any number of arguments of any type. Only constraint is that Object arguments should have __toString defined (Not applicable to instances of tag model).

*Usage example:*

```php
$user->tags->remove( 'test', Array( 'admin', 10 ), $user )->save();
 
// This code will detach from the user follwoing tags: 'test', 'admin', '10', 'User_1' ($user->__toString())
```

### reset

```php
public CActiveRecord reset( void )
```

Detaches all tags from the model.

*Usage example:*

```php
$user->tags->reset()->save();
```

### set

```php
public CActiveRecord set( $... )
```

Attaches to the model specified set of tags that will replace all previous ones.

Can be called with any number of arguments of any type. Only constraint is that Object arguments should have __toString defined (Not applicable to instances of tag model).

*Usage example:*

```php
// attaching of tags 'test', 'admin', '10'
$user->tags->add( 'test', Array( 'admin', 10 ) );
 
// removing all previously attached tags ('test', 'admin', '10')
// and attaching new ones: 'newTest', 'Array' и 'blah'
$user->tags->set( Array( 'newTest', Array( 11 ) ), 'blah' )->save();
```

### taggedWith

```php
public CActiveRecord taggedWith( $... )
```

Modifies the model DB criteria in order to find models with any of specidied tags attached.

Can be called with any number of arguments of any type. Only constraint is that Object arguments should have __toString defined (Not applicable to instances of tag model).

Model will be selected if it has AT LEAST ONE of the specified tags attached.

*Usage example:*

```php
$userList = User::model()->tags->taggedWith( 'test, admin' )->findAll();
```

### __toString

```php
public string __toString( void )
```

Allows all attached to the model tags to be printed imploded by separator.

```php

// attaching of tags 'test', 'admin', '10'
$user->tags->add( 'test', Array( 'admin', 10 ) );
 
print $user->tags;
 
// will be printed: 'test,admin,10'
```
