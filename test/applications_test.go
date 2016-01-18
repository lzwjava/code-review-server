package codereview

import (
	"testing"
	"net/url"
	"github.com/stretchr/testify/assert"
	"fmt"
)

func TestApplications_create(t *testing.T) {
	c := NewClient()
	learner := registerLearner(c)
	learnerId := learner["id"].(string)
	res := c.postData("applications", url.Values{});
	assert.NotNil(t, res["applicationId"], res["created"]);
	assert.Equal(t, learnerId, res["learnerId"]);
}

func TestApplications_agree(t *testing.T) {
	c := NewClient()
	learner := registerLearner(c)
	learnerId := learner["id"].(string)
	application := c.postData("applications", url.Values{});
	applicationId := floatToStr(application["applicationId"])
	c.admin = true;
	res := c.getData("applications/" + applicationId + "/agree", url.Values{})
	assert.NotNil(t, res)

	fmt.Println(learnerId)

	c.admin = false;

	reviewer := login(c, learner["mobilePhoneNumber"].(string), "123456")
	assert.Equal(t, learner["username"], reviewer["username"])
	assert.Equal(t, learner["id"], reviewer["id"])
	assert.Equal(t, learner["avatarUrl"], reviewer["avatarUrl"])
	assert.Equal(t, "reviewer", reviewer["type"])
	assert.Equal(t, 8, toInt(reviewer["maxOrders"]))
}
