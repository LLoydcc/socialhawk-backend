<?php
/**
 * Created by PhpStorm.
 * User: levent
 * Date: 15.02.19
 * Time: 09:39
 */

namespace App\Influencer\Campaign\Model;

use App\System\Core\Setup\Database;

class AllCampaigns
{
    /**
     * @var Database
     */
    protected $_database;
    protected $_databaseConnection;

    public function __construct()
    {
        $this->_database = new Database();
    }

    /**
     * @return array|bool|mixed
     */
    public function getAllCampaigns()
    {
        $conn = $this->_database->connectToDatabase();
        if ($conn === false) {
            return false;
        }

        //Todo: change thumbnail when implemented @levent
        $result = $conn->query("
SELECT
    campaigns.id,
    advertiser_users.company_name,
    campaigns.campaign_url_hash AS campaign_hash,
    campaigns.campaign_title AS title,
    campaigns.campaign_desc AS description,
    campaigns.creation_date,
    campaigns.expiration_date,
    GROUP_CONCAT(hashtags.tag) AS hashtags
FROM
    campaigns
    LEFT JOIN advertiser_users 
      ON advertiser_users.id = campaigns.advertiser_id
    LEFT JOIN campaigns_hashtags 
      ON campaigns_hashtags.campaign_id = campaigns.id
    LEFT JOIN hashtags 
      ON hashtags.id = campaigns_hashtags.hashtag_id
WHERE
    campaigns.active = 1
GROUP BY
	campaigns.id;
");

        // make id of campaign the key of campaigns array
        $campaigns = [];
        while ($campaign = $result->fetch_assoc()) {
            $id = $campaign['id'];
            unset($campaign['id']);
            $campaigns[$id] = $campaign;
            $campaigns[$id]['rewards'] = $this->getCampaignRewards($id);
            $campaigns[$id]['thumbnail'] = $this->getCampaignThumbnail($id);
            $campaigns[$id]['description'] = utf8_encode($campaign['description']);
            $campaigns[$id]['title'] = utf8_encode($campaign['title']);
        }

        if ($result->num_rows > 0) {
            return $campaigns;
        }

        return false;
    }

    /**
     * @param $campaignId
     * @return bool|string
     */
    private function getCampaignRewards($campaignId)
    {
        $conn = $this->_database->connectToDatabase();
        if ($conn === false) {
            return false;
        }

        $rewards_sql = $conn->prepare("
SELECT DISTINCT GROUP_CONCAT(rewards.type) as rewards_type
FROM campaigns_rewards
LEFT JOIN rewards
      ON rewards.id = campaigns_rewards.rewards_id
WHERE campaigns_rewards.campaign_id = ?
            ");
        $rewards_sql->bind_param("i", $a);
        $a = $campaignId;
        $rewards_sql->execute();
        $rewards = $rewards_sql->get_result()->fetch_all();
        return $rewards[0][0];
    }

    /**
     * @param $campaignId
     * @return bool
     */
    private function getCampaignThumbnail($campaignId)
    {
        $conn = $this->_database->connectToDatabase();
        if ($conn === false) {
            return false;
        }

        $thumbnail_sql = $conn->prepare("
        SELECT thumbnails FROM campaigns_images WHERE campaign_id = ?;
        ");

        $thumbnail_sql->bind_param("i", $a);
        $a = $campaignId;
        $thumbnail_sql->execute();
        $thumbnail = $thumbnail_sql->get_result()->fetch_all();
        return $thumbnail[0][0];

    }

}