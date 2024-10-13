<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>variable</title>
</head>
<body>
    <?php
        include("connect.php");

        // 檢查是否設置了查詢條件
        if (!isset($where_condition)) {
            die("No query condition specified.");
        }

        // 聯合查詢，使用傳遞的查詢條件
        $sql = "
            SELECT *
            FROM 
                additional a
            JOIN 
                detail d ON a.r_id = d.r_id
            JOIN 
                review r ON a.r_id = r.r_id
            WHERE 
                $where_condition
        ";

        $result = $conn->query($sql);
        
        $restaurants = []; // 用於存儲多家餐廳的信息

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // 設置變數 
                // $restaurant = [
                //     //detail
                //     'r_id' => $row['r_id'],
                //     'r_name' => $row['r_name'],
                //     'r_latitude' => $row['r_latitude'],
                //     'r_longitude' => $row['r_longitude'],
                //     'r_phone' => $row['r_phone'],
                //     'r_website' => $row['r_website'],
                //     'r_rating' => $row['r_rating'],
                //     'num_reviews' => $row['num_reviews'],
                //     'price_level' => $row['price_level'],
                //     'features' => $row['features'],
                //     'cuisine' => $row['cuisine'],
                //     'trip_types' => $row['trip_types'],
                //     'r_address' => $row['r_address'],
                //     'r_postalcode' => $row['r_postalcode'],
                //     'r_geo_rank_id' => $row['ranking_data_geo_location_id'],
                //     'r_geo_rank' => $row['r_ranking_data_ranking_string'],
                //     'r_geo_name' => $row['ranking_data_geo_location_name'],
                //     'r_ranking_out_of' => $row['r_ranking_out_of'],
                //     'r_ranking_data_rank' => $row['r_ranking_data_rank'],
                //     'r_food_rating_name' => $row['subratings_0_localized_name'],
                //     'r_food_rating' => $row['r_rating_food'],
                //     'r_atmosphere_rating_name' => $row['subratings_1_localized_name'],
                //     'r_atmosphere_rating' => $row['r_rate_atmosphere'],
                //     'r_service_rating_name' => $row['subratings_2_localized_name'],
                //     'r_service_rating' => $row['r_rate_service'],
                //     'r_value_rating_name' => $row['subratings_3_localized_name'],
                //     'r_value_rating' => $row['r_rate_value'],
                //     'r_clean_rating_name' => $row['subratings_4_localized_name'],
                //     'r_clean_rating' => $row['r_rate_clean'],
                //     'r_hours_periods' => $row['r_hours_periods'],
                //     'r_hours_weekday' => $row['r_hours_weekday'],

                //     //additional_
                //     'r_round_table' => $row['r_round_table'],
                //     'r_round_table_num' => $row['r_round_table_num'],
                //     'r_long_table' => $row['r_long_table'],
                //     'r_long_table_num' => $row['r_long_table_num'],
                //     'r_MRT' => $row['r_MRT'],
                //     'r_MRT_dist_km' => $row['r_MRT_dist_km'],
                //     'r_LRT' => $row['r_LRT'],
                //     'r_LRT_dist_km' => $row['r_LRT_dist_km'],
                //     'r_price_low' => $row['r_price_low'],
                //     'r_price_high' => $row['r_price_high'],
                //     'r_time_low' => $row['r_time_low'],
                //     'r_time_high' => $row['r_time_high'],
                //     'r_time_special' => $row['r_time_special'],
                //     'r_min_consumption' => $row['r_min_consumption'],
                //     'r_price_min' => $row['r_price_min'],
                //     'r_has_parking' => $row['r_has_parking'],
                //     'r_near_parking' => $row['r_near_parking'],
                //     'r_reserve' => $row['r_reserve'],
                //     'r_vibe' => $row['r_vibe'],
                //     'r_food_desc' => $row['r_food_desc'],
                //     'r_food_dishes' => $row['r_food_dishes'],
                //     'food_comment_sum' => $row['food_comment_sum'],
                //     'atmosphere_comment_sum' => $row['atmosphere_comment_sum'],
                //     'special_comment_sum' => $row['special_comment_sum'],
                //     'notice_comment_sum' => $row['notice_comment_sum'],
                //     'r_photo_env1' => $row['r_photo_env1'],
                //     'r_photo_env2' => $row['r_photo_env2'],
                //     'r_photo_env3' => $row['r_photo_env3'],
                //     'r_photo_food1' => $row['r_photo_food1'],
                //     'r_photo_food2' => $row['r_photo_food2'],
                //     'r_photo_food3' => $row['r_photo_food3'],
                //     'r_photo_food4' => $row['r_photo_food4'],
                //     'r_photo_food5' => $row['r_photo_food5'],
                //     'r_photo_door' => $row['r_photo_door'],
                //     'r_photo_menu1' => $row['r_photo_menu1'],
                //     'r_photo_menu2' => $row['r_photo_menu2'],
                //     'r_photo_menu3' => $row['r_photo_menu3'],
                //     'r_photo_menu4' => $row['r_photo_menu4'],
                //     'r_photo_menu5' => $row['r_photo_menu5'],
                //     'r_photo_menu6' => $row['r_photo_menu6'],
                //     'r_photo_menu7' => $row['r_photo_menu7'],
                //     'r_photo_menu8' => $row['r_photo_menu8'],
                //     'r_photo_menu9' => $row['r_photo_menu9'],
                //     'r_photo_menu10' => $row['r_photo_menu10'],
                //     'r_photo_menu11' => $row['r_photo_menu11'],
                //     'r_photo_menu12' => $row['r_photo_menu12'],
                //     'r_photo_menu13' => $row['r_photo_menu13'],
                //     'r_photo_menu14' => $row['r_photo_menu14'],
                //     'r_photo_menu15' => $row['r_photo_menu15'],
                //     'r_photo_menu16' => $row['r_photo_menu16'],

                //     //review
                //     'review_1_author' => $row['review_1_author'],
                //     'review_1_rating' => $row['review_1_rating'],
                //     'review_1_text' => $row['review_1_text'],
                //     'review_1_title' => $row['review_1_title'],
                //     'review_1_trip_type' => $row['review_1_trip_type'],
                //     'review_1_travel_date' => $row['review_1_travel_date'],
                //     'review_2_author' => $row['review_2_author'],
                //     'review_2_rating' => $row['review_2_rating'],
                //     'review_2_text' => $row['review_2_text'],
                //     'review_2_title' => $row['review_2_title'],
                //     'review_2_trip_type' => $row['review_2_trip_type'],
                //     'review_2_travel_date' => $row['review_2_travel_date'],
                //     'review_3_author' => $row['review_3_author'],
                //     'review_3_rating' => $row['review_3_rating'],
                //     'review_3_text' => $row['review_3_text'],
                //     'review_3_title' => $row['review_3_title'],
                //     'review_3_trip_type' => $row['review_3_trip_type'],
                //     'review_3_travel_date' => $row['review_3_travel_date'],
                //     'review_4_author' => $row['review_4_author'],
                //     'review_4_rating' => $row['review_4_rating'],
                //     'review_4_text' => $row['review_4_text'],
                //     'review_4_title' => $row['review_4_title'],
                //     'review_4_trip_type' => $row['review_4_trip_type'],
                //     'review_4_travel_date' => $row['review_4_travel_date'],
                //     'review_5_author' => $row['review_5_author'],
                //     'review_5_rating' => $row['review_5_rating'],
                //     'review_5_text' => $row['review_5_text'],
                //     'review_5_title' => $row['review_5_title'],
                //     'review_5_trip_type' => $row['review_5_trip_type'],
                //     'review_5_travel_date' => $row['review_5_travel_date']
                // ];
                $restaurants[] = $row;
                // $restaurants[] = $restaurant; // 將當前餐廳信息添加到數組中
            }
        } else {
            echo "0 results";
        }

        $conn->close();
        
    ?>
</body>
</html>