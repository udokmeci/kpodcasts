#!/bin/bash
cd outputs
for ((i=0;i<10000;i++)); 
do   
     	if [ -f $i.json ]; 
                then
                    	echo $i.json
                        RES=$(jq .result  < $i.json)
                        echo $RES
                        if [ "$RES" == "true" ]; 
                        then
                            	echo 'true';
                        else
                            	rm $i.json;
                        fi
                        #sleep 3;
                else
                    	echo "$i exists";
        fi
done






