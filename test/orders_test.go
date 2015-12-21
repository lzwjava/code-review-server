package codereview

import (
	"testing"
	"github.com/stretchr/testify/assert"
	_ "fmt"
	"net/url"
	_ "encoding/json"
	_ "strings"
	"fmt"
	_ "flag"
)

func TestOrders_AddOrder(t *testing.T) {
	c := NewClient()
	addOrder(c, t)
}

func addOrder(c *Client, t *testing.T) (map[string]interface{}, map[string]interface{}, map[string]interface{}) {
	reviewer := registerReviewer(c)
	learner := registerLearner(c)

	reviewerId := reviewer["id"].(string);
	learnerId := learner["id"].(string);

	order := c.callData("orders/add", url.Values{"gitHubUrl": {"https://github.com/lzwjava/Reveal-In-GitHub"},
		"remark": {"麻烦大神了"}, "reviewerId":{reviewerId}});
	assert.Equal(t, "https://github.com/lzwjava/Reveal-In-GitHub", order["gitHubUrl"])
	assert.Equal(t, "麻烦大神了", order["remark"].(string));
	assert.Equal(t, reviewerId, order["reviewerId"]);
	assert.Equal(t, learnerId, order["learnerId"]);
	assert.Equal(t, 0, toInt(order["status"]));
	assert.NotNil(t, order["created"]);
	assert.NotNil(t, order["updated"]);
	assert.NotNil(t, order["orderId"])
	assert.Nil(t, order["reviewId"]);
	return reviewer, learner, order;
}

func testCallbackStr(orderNo string) string {
	const jsonStream = `{ "id": "evt_ugB6x3K43D16wXCcqbplWAJo", "created": 1427555101, "livemode": true, "type":
	"charge.succeeded", "data": { "object": { "id": "ch_Xsr7u35O3m1Gw4ed2ODmi4Lw", "object": "charge", "created":
	1427555076, "livemode": true, "paid": true, "refunded": false, "app": "app_1Gqj58ynP0mHeX1q", "channel": "upacp", "order_no": "%s", "client_ip": "127.0.0.1", "amount": 100, "amount_settle": 0, "currency": "cny", "subject": "Your Subject", "body": "Your Body", "extra": {}, "time_paid": 1427555101, "time_expire": 1427641476, "time_settle": null, "transaction_no": "1224524301201505066067849274", "refunds": { "object": "list", "url": "/v1/charges/ch_L8qn10mLmr1GS8e5OODmHaL4/refunds", "has_more": false, "data": [] }, "amount_refunded": 0, "failure_code": null, "failure_msg": null, "metadata": {}, "credential": {}, "description": null } }, "object": "event", "pending_webhooks": 0, "request": "iar_qH4y1KbTy5eLGm1uHSTS00s" }`
	out := fmt.Sprintf(jsonStream, orderNo);
	//	var tmp interface{}
	//	json.NewDecoder(strings.NewReader(out)).Decode(&tmp)
	//	jsonBytes, jsonErr := json.Marshal(tmp)
	//	checkErr(jsonErr)
	//	jsonStr := string(jsonBytes[:])
	return out
}

func TestJson(t *testing.T) {
	fmt.Println(testCallbackStr("101"));
}
