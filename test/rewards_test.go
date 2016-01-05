package codereview

import (
	"testing"
	_"database/sql"
	_ "github.com/go-sql-driver/mysql"
	_ "github.com/stretchr/testify/assert"
	_ "fmt"
	"github.com/stretchr/testify/assert"
	"net/url"
	"fmt"
)

func TestOrders_Reward(t *testing.T) {
	c := NewClient()
	_, learner, order := addOrder(c, t)

	c.sessionToken = learner["sessionToken"].(string)
	orderId := floatToStr(order["orderId"])

	rewardRes := c.call("orders/reward", url.Values{"orderId": {orderId},
		"amount": {"100"}})
	assert.NotNil(t, rewardRes)
	assert.Equal(t, 16, toInt(rewardRes["code"]));
	assert.Equal(t, "申请者打赏金额至少为 5 元(amount=500)", rewardRes["error"].(string));

	rewardRes = c.call("orders/reward", url.Values{"orderId": {orderId},
		"amount": {"500"}})

	orderNo := rewardRes["order_no"].(string)
	callbackRes := c.callWithStr("rewards/callback", testCallbackStr(orderNo, orderId, 5000))
	assert.Equal(t, toInt(callbackRes["code"]), 0);

	theOrder := c.getData("orders/view", url.Values{"orderId":{orderId}})
	assert.Equal(t, "paid", theOrder["status"])
}

func TestRewards_Count(t *testing.T) {
	c := NewClient()
	reviewer, _, order := addOrder(c, t)
	orderId := floatToStr(order["orderId"])
	reward(c, orderId, t);
	reward(c, orderId, t);
	afterReviewer := c.getData("reviewers/view", url.Values{"id": {reviewer["id"].(string)}});
	assert.Equal(t, toInt(afterReviewer["rewardCount"]), 2);
}

func reward(c *Client, orderId string, t *testing.T) {
	rewardRes := c.call("orders/reward", url.Values{"orderId": {orderId},
		"amount": {"500"}})
	orderNo := rewardRes["order_no"].(string)
	callbackRes := c.callWithStr("rewards/callback", testCallbackStr(orderNo, orderId, 500))
	assert.Equal(t, toInt(callbackRes["code"]), 0);
}

func testCallbackStr(orderNo string, orderId string, amount int) string {
	const jsonStream = `{ "id": "evt_ugB6x3K43D16wXCcqbplWAJo", "created": 1427555101, "livemode": true, "type":
	"charge.succeeded", "data": { "object": { "id": "ch_Xsr7u35O3m1Gw4ed2ODmi4Lw", "object": "charge", "created":
	1427555076, "livemode": true, "paid": true, "refunded": false, "app":
	"app_1Gqj58ynP0mHeX1q",
	"channel":
	"upacp", "metadata": { "orderId": %s }, "order_no": "%s", "client_ip": "127.0.0.1", "amount": %d, "amount_settle":
	0, "currency": "cny", "subject": "Your Subject", "body": "Your Body", "extra": {}, "time_paid": 1427555101, "time_expire": 1427641476, "time_settle": null, "transaction_no": "1224524301201505066067849274", "refunds": { "object": "list", "url": "/v1/charges/ch_L8qn10mLmr1GS8e5OODmHaL4/refunds", "has_more": false, "data": [] }, "amount_refunded": 0, "failure_code": null, "failure_msg": null, "credential": {}, "description": null } }, "object": "event", "pending_webhooks": 0, "request": "iar_qH4y1KbTy5eLGm1uHSTS00s" }`
	out := fmt.Sprintf(jsonStream, orderId, orderNo, amount);
	//	var tmp interface{}
	//	json.NewDecoder(strings.NewReader(out)).Decode(&tmp)
	//	jsonBytes, jsonErr := json.Marshal(tmp)
	//	checkErr(jsonErr)
	//	jsonStr := string(jsonBytes[:])
	return out
}

func TestJson(t *testing.T) {
	fmt.Println(testCallbackStr("111", "101", 500));
}
