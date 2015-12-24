package codereview
import (
	"testing"
	"net/url"
	"reflect"
	"fmt"
	"errors"
	"github.com/stretchr/testify/assert"
	"encoding/json"
)

func SetField(obj interface{}, name string, value interface{}) error {
	structValue := reflect.ValueOf(obj).Elem()

	if structValue.Kind() != reflect.Struct {
		return fmt.Errorf("It's not Struct")
	}

	structFieldValue := structValue.FieldByName(name)

	if !structFieldValue.IsValid() {
		return fmt.Errorf("No such field: %s in obj", name)
	}

	if !structFieldValue.CanSet() {
		return fmt.Errorf("Cannot set %s field value", name)
	}

	structFieldType := structFieldValue.Type()
	val := reflect.ValueOf(value)
	if structFieldType != val.Type() {
		return errors.New("Provided value type didn't match obj field type")
	}

	structFieldValue.Set(val)
	return nil
}


func FillStruct(obj interface{}, m interface{}) error {
	mapObj, ok := m.(map[string]interface{})
	if !ok {
		panic("please input map[string]interface{}")
	}

	for k, v := range mapObj {
		err := SetField(obj, k, v)
		if err != nil {
			return err
		}
	}
	return nil
}

type Tag struct {
	TagId   int
	TagName string
	Color   string
}

func convertToStruct(mapObj interface{}, structObj interface{}) {
	jsonBytes, err := json.Marshal(mapObj)
	checkErr(err)
	err = json.Unmarshal(jsonBytes, structObj)
	checkErr(err)
}

func getTag() Tag {
	c := NewClient()
	tags := c.getArrayData("tags", url.Values{})
	tag := tags[0]
	myTag := Tag{}
	convertToStruct(tag, &myTag)
	return myTag
}

func TestTags_Get(t *testing.T) {
	tag := getTag()
	assert.NotNil(t, tag)
	assert.True(t, tag.TagId > 0)
	assert.NotNil(t, tag.TagName)
	assert.NotNil(t, tag.Color)
	//StructTest()
}

func StructTest() {
	type t struct {
		N int
	}
	var n = t{42}
	// N at start
	fmt.Println(n.N)
	// pointer to struct - addressable
	ps := reflect.ValueOf(&n)
	// struct
	s := ps.Elem()
	if s.Kind() == reflect.Struct {
		// exported field
		f := s.FieldByName("N")
		if f.IsValid() {
			// A Value can be changed only if it is
			// addressable and was not obtained by
			// the use of unexported struct fields.
			if f.CanSet() {
				// change value of N
				if f.Kind() == reflect.Int {
					x := int64(7)
					if !f.OverflowInt(x) {
						f.SetInt(x)
					}
				}
			}
		}
	}
	// N at end
	fmt.Println(n.N)
}

func TestTags_AddUserTag(t *testing.T) {
	c := NewClient()
	registerLearner(c)
	tag := getTag()
	tags := c.callArrayData("user/tag", url.Values{"op":{"add"}, "tagId":{fmt.Sprintf("%d", tag.TagId)}})
	assert.Equal(t, 1, len(tags))

	learner := c.callData("user/self", url.Values{})
	fmt.Println(learner)

	tags = c.callArrayData("user/tag", url.Values{"op":{"remove"}, "tagId":{fmt.Sprintf("%d", tag.TagId)}})
	assert.Equal(t, 0, len(tags))
}
