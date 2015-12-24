package codereview
import (
	"testing"
	"os"
	"fmt"
)

func TestMain(m *testing.M) {
	cleanTables()
	os.Exit(m.Run())
}

func cleanTables() {
	tables := []string{"reviewers", "learners", "orders", "rewards", "charges", "reviews",
		"orders_tags", "users_tags"}
	for _, table := range tables {
		deleteTable(table)
	}
	fmt.Println()
}
