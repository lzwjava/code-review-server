package codereview

import (
	"testing"
	"github.com/stretchr/testify/assert"
	_ "fmt"
	"net/url"
	_ "encoding/json"
	_ "strings"
	"fmt"
	_"encoding/json"
	_"strings"
)

func TestOrders_Add(t *testing.T) {
	c := NewClient()

	reviewer := registerReviewer(c)
	learner := registerLearner(c)

	res := c.callData("orders/add", url.Values{"gitHubUrl": {"https://github.com/lzwjava/Reveal-In-GitHub"},
		"remark": {"麻烦大神了"}, "reviewerId": {reviewer["id"].(string)}})
	assert.Equal(t, "https://github.com/lzwjava/Reveal-In-GitHub", res["gitHubUrl"])
	assert.NotNil(t, res["orderId"])

	c.sessionToken = reviewer["sessionToken"].(string)
	orderId := floatToStr(res["orderId"])
	reviewRes := c.callData("reviews/add", url.Values{"orderId": {orderId},
		"content": {"代码写得不错！"}})
	assert.NotNil(t, reviewRes["reviewId"])
	assert.Equal(t, "代码写得不错！", reviewRes["content"])

	reviewId := floatToStr(reviewRes["reviewId"])
	editRes := c.callData("reviews/edit", url.Values{"reviewId": {reviewId},
		"content": {"这里有几个问题。"}})
	assert.Equal(t, "这里有几个问题。", editRes["content"])

	rewardRes, err := c.call("reviews/reward", url.Values{"reviewId": {reviewId},
		"amount": {"1000"}})
	checkErr(err)
	assert.NotNil(t, rewardRes)

	orderNo := rewardRes["order_no"].(string)
	c.callWithStr("rewards/callback", testCallbackStr(orderNo))

	deleteRecord("orders", "orderId", orderId)
	deleteRecord("reviews", "reviewId", reviewId)
	deleteRecord("rewards", "orderNo", orderNo)

	deleteUserByData(learner)
	deleteUserByData(reviewer)
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
