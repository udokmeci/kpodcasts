#!/bin/bash
cd outputs
COUNTER=0
for ((i=5006;i<10000;i++)); 
do   
	if [ ! -f $i.json ]; 
		then
			echo $i.json
			curl -s "http://handlers.karnaval.com/api.functions.php?command=get_show_episode&episode_id=$i" -o "$i.json";
			RES=$(jq .result  < $i.json)
			echo $RES
			if [ "$RES" == "true" ]; 
			then
				echo 'true';
			else
				let COUNTER=COUNTER+1 
				rm $i.json;
				if [ $COUNTER -gt 5 ]; 
				then
					exit;
				fi
			fi
			#sleep 3;
		else
			echo "$i exists";
	fi
done


