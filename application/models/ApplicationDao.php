<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 16/1/18
 * Time: 下午1:23
 */
class ApplicationDao extends BaseDao
{
    function addApplication($learnerId)
    {
        $data = array(
            KEY_LEARNER_ID => $learnerId
        );
        $this->db->insert(TABLE_APPLICATIONS, $data);
        return $this->db->insert_id();
    }

    private function publicFields()
    {
        return $this->mergeFields(array(KEY_APPLICATION_ID, KEY_LEARNER_ID, KEY_CREATED));
    }

    function viewApplication($applicationId)
    {
        $fields = $this->publicFields();
        return $this->getOneFromTable(TABLE_APPLICATIONS, KEY_APPLICATION_ID, $applicationId, $fields);
    }

    function agreeApplication($applicationId)
    {
        $application = $this->viewApplication($applicationId);
        if (!$application) {
            return false;
        }
        $sql = "SELECT * FROM learners WHERE id=?";
        $learnerId = $application->learnerId;
        $array[] = $learnerId;
        $this->db->trans_start();
        $learner = $this->db->query($sql, $array)->row_array();
        $learner[KEY_TYPE] = TYPE_REVIEWER;
        $learner[KEY_VALID] = 1;
        $ok = $this->db->insert(TABLE_REVIEWERS, $learner);
        if ($ok) {
            $this->db->delete(TABLE_LEARNERS, array(KEY_ID => $learnerId));
            $this->db->delete(TABLE_APPLICATIONS, array(KEY_APPLICATION_ID => $applicationId));
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
