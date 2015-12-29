package codereview

import (
	"fmt"
	_ "reflect"
	"net/url"
	"database/sql"
	"strconv"
	"crypto/md5"
)

func checkErr(err error) {
	if err != nil {
		panic(err)
	}
}


func registerLearner(c *Client) map[string]interface{} {
	res := c.call("user/register", url.Values{"mobilePhoneNumber": {"1326163092"},
		"username": {"lzwjavaTest"}, "smsCode": {"5555"}, "password":{md5password("123456")}, "type": {"0"}})
	if (toInt(res["code"]) == 0) {
		registerRes := res["result"].(map[string]interface{})
		c.sessionToken = registerRes["sessionToken"].(string)
		return registerRes
	} else {
		loginRes := c.callData("user/login", url.Values{"mobilePhoneNumber": {"1326163092"},
			"password":{md5password("123456")}});
		return loginRes
	}
}

func registerReviewer(c *Client) map[string]interface{} {
	res := c.call("user/register", url.Values{"mobilePhoneNumber": {"13261630924"},
		"username": {"lzwjavaReviewer"}, "smsCode": {"5555"}, "password":{md5password("123456")}, "type": {"1"}})
	if (toInt(res["code"]) == 0) {
		registerRes := res["result"].(map[string]interface{})
		c.sessionToken = registerRes["sessionToken"].(string)
		validReviewer(c, registerRes["id"].(string))
		return registerRes
	} else {
		loginRes := c.callData("user/login", url.Values{"mobilePhoneNumber": {"13261630924"},
			"password":{md5password("123456")}});
		return loginRes
	}
}

func registerUsers(c *Client) (map[string]interface{}, map[string]interface{}) {
	reviewer := registerReviewer(c)
	learner := registerLearner(c)
	return reviewer, learner
}

func md5password(password string) string {
	data := []byte(password)
	return fmt.Sprintf("%x", md5.Sum(data))
}

func validReviewer(c *Client, reviewerId string) {
	c.getData("reviewers/valid", url.Values{"id": {reviewerId}})
}

func deleteTable(table string) {
	deleteRecord(table, "1", "1");
}

func deleteRecord(table string, column string, id string) {
	db, err := sql.Open("mysql", "lzw:@/codereview")
	checkErr(err)

	err = db.Ping()
	checkErr(err)

	sql := fmt.Sprintf("delete from %s where %s=?", table, column)

	stmt, err := db.Prepare(sql)
	checkErr(err)

	res, err := stmt.Exec(id)
	checkErr(err)

	affect, err := res.RowsAffected()
	checkErr(err)

	fmt.Println(sql, "affected", affect)

	db.Close()
}

func toInt(obj interface{}) (int) {
	return int(obj.(float64))
}

func floatToStr(flt interface{}) string {
	return strconv.Itoa(toInt(flt))
}
