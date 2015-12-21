package codereview

import (
	"fmt"
	_ "reflect"
	"net/url"
	"database/sql"
	"strconv"
)

func checkErr(err error) {
	if err != nil {
		panic(err)
	}
}

func deleteUser(mobilePhoneNumber string) {
	c := NewClient()
	_, err := c.call("user/delete", url.Values{"mobilePhoneNumber": {mobilePhoneNumber}})
	if err != nil {
		panic(err)
	}
}

func deleteUserByData(data map[string]interface{}) {
	deleteUser(data["mobilePhoneNumber"].(string))
}

func registerLearner(c *Client) map[string]interface{} {
	deleteUser("1326163092")
	res := c.callData("user/register", url.Values{"mobilePhoneNumber": {"1326163092"},
		"username": {"lzwjavaTest"}, "smsCode": {"5555"}, "password":{"123456"}, "type": {"0"}})
	return res
}

func registerReviewer(c *Client) map[string]interface{} {
	deleteUser("13261630924")
	res := c.callData("user/register", url.Values{"mobilePhoneNumber": {"13261630924"},
		"username": {"lzwjavaReviewer"}, "smsCode": {"5555"}, "password":{"123456"}, "type": {"1"}})
	validReviewer(c, res["id"].(string))
	return res
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
