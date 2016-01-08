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

func TestTags_Get(t *testing.T) {
	tag := getTag()
	assert.NotNil(t, tag)
	assert.True(t, tag.TagId > 0)
	assert.NotNil(t, tag.TagName)
	assert.NotNil(t, tag.Color)
	//StructTest()
}

func TestTags_AddUserTag(t *testing.T) {
	c := NewClient()
	registerLearner(c)
	tag := getTag()
	tagId := fmt.Sprintf("%d", tag.TagId)
	tags := c.postArrayData("user/tags", url.Values{"tagId":{tagId}})
	assert.Equal(t, 1, len(tags))

	learner := c.getData("user/self", url.Values{})
	assert.Equal(t, 1, len(learner["tags"].([]interface{})))

	tags = c.deleteArrayData("user/tags/" + tagId)
	assert.Equal(t, 0, len(tags))

	learner = c.getData("user/self", url.Values{})
	assert.NotNil(t, learner["tags"].([]interface{}));
}

type Order struct {
	OrderId int
}

type Review struct {
	ReviewId int
}

func TestTags_AddReviewTag(t *testing.T) {
	c := NewClient()
	tag := getTag()
	_, _, _, review := addOrderAndReview(c)
	myReview := Review{}
	convertToStruct(review, &myReview)
	reviewId := floatToStr(myReview.ReviewId)
	tagId := fmt.Sprintf("%d", tag.TagId)
	tags := c.postArrayData("reviews/" + reviewId + "/tags", url.Values{"tagId":{tagId}});
	assert.Equal(t, 1, len(tags))

	theReview := c.getData("reviews/" + reviewId, url.Values{})
	assert.Equal(t, 1, len((theReview["tags"]).([]interface{})))

	res := c.delete("reviews/" + reviewId + "/tags/" + tagId)
	assert.Equal(t, 0, len(res["result"].([]interface{})))
}
