<?

//error_log(print_r($_POST, TRUE));

$domain = $_POST["domain"];

if( $domain == "city_tour" ) {
	
	
	echo <<<END
	{
		"total":8,
		"rows":[{
			"id":"1",
			"description":"Purchasing 4 tickets of Downtown Tour",
			"tour_name":"Downtown Tour",
			"Ticket_Number":"4",
			"visiting_place":"*"
		}, {
			"id": "2",
			"description": "Purchasing 1 ticket of  Downtown Tour",
			"tour_name": "Downtown Tour",
			"ticket_number": "1",
			"visiting_place": "*"
		}, {
			"id": "3",
			"description": "Purchasing 3 tickets of All Around Tour",
			"tour_name": "All Around Tour",
			"ticket_number": "3",
			"visiting_place": "*"
		}, {
			"id": "4",
			"description": "Purchasing 1 ticket of All Around Tour",
			"tour_name": "All Around Tour",
			"ticket_number": "1",
			"visiting_place": "*"
		}, {
			"id": "5",
			"description": "Purchasing 2 tickets to go to Harlem Market",
			"tour_name": "*",
			"ticket_number": "2",
			"visiting_place": "Harlem Market"
		}, {
			"id": "6",
			"description": "Purchasing 4 tickets to go to the Statue of Liberty",
			"tour_name": "*",
			"ticket_number": "4",
			"visiting_place": "the Statue of Liberty"
		}, {
			"id": "7",
			"description": "Purchasing 3 tickets to go to the Metropolitan Museum Art",
			"tour_name": "*",
			"ticket_number": "3",
			"visiting_place": "the Metropolitan Museum Art"
		}, {
			"id": "8",
			"description": "Purchasing 1 ticket to go to Harlem Market",
			"tour_name": "*",
			"ticket_number": "1",
			"visiting_place": "Harlem Market"
		}]
	}
END;

} else if( $domain == "immigration" ) {
	echo <<<END
	{
		"total":8,
		"rows":[{
			"id":"1",
			"description":"Purchasing 4 tickets of Downtown Tour",
			"tour_name":"Downtown Tour",
			"Ticket_Number":"4",
			"visiting_place":"*"

		}]
	}
END;
	
}


?>