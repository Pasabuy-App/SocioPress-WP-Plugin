# SocioPress - WordPress Plugin

In this project, we will try to make our RestAPI plugin focused on Social Networking. That's it for now and Good Luck!


#### PROFILE FEED LIST => /datavice/api/v1/feeds/profile
REQUEST:
- Method: GET
- Header: key=Content-Type, value=application/x-www-form-urlencoded
- Params: key=wpid (value=User ID), key=snky (value=Session Key)

RESPONSE:
- code: 'unknown' message: 'Please contact your administrator. Request unknown!' data: null
- code: 'failed' message: 'Please contact your administrator. ID not in valid format!' data: null
- code: 'failed' message: 'User not found!' data: null

IF SUCCESS:
```
    {
       {
        "status": "success",
        "data": {
                "list": [
                    {
                        "id": "15"
                    },
                    {
                        "id": "14"
                    },
                    {
                        "id": "13"
                    },
                    {
                        "id": "12"
                    },
                    {
                        "id": "11"
                    },
                    {
                        "id": "10"
                    },
                    {
                        "id": "9"
                    },
                    {
                        "id": "8"
                    },
                    {
                        "id": "7"
                    },
                    {
                        "id": "6"
                    },
                    {
                        "id": "5"
                    },
                    {
                        "id": "4"
                    }
                ],
                "last_id": {
                    "id": "4"
                }
            }
        }
    }
```

#### ADDITIONAL PROFILE FEED LIST => /datavice/api/v1/feeds/p_feeds
REQUEST:
- Method: GET
- Header: key=Content-Type, value=application/x-www-form-urlencoded
- Params: key=wpid (value=User ID), key=snky (value=Session Key), key=LID (value=Last ID of feed list)

RESPONSE:
- code: 'unknown' message: 'Please contact your administrator. Request unknown!' data: null
- code: 'failed' message: 'Parameters not in valid format!' data: null
- code: 'failed' message: 'User not found!' data: null
- code: 'failed' message: 'No more posts to see!' data: null


IF SUCCESS:
```
    {
        "status": "success",
        "data": {
            "list": [
                {
                    "id": "5"
                },
                {
                    "id": "4"
                },
                {
                    "id": "3"
                },
                {
                    "id": "2"
                },
                {
                    "id": "1"
                }
            ],
            "last_id": {
                "id": "1"
            }
        }
    }
```

#### HOME FEED LIST => /datavice/api/v1/feeds/home
REQUEST:
- Method: GET
- Header: key=Content-Type, value=application/x-www-form-urlencoded
- Params: key=wpid (value=User ID), key=snky (value=Session Key)

RESPONSE:
- code: 'unknown' message: 'Please contact your administrator. Request unknown!' data: null
- code: 'failed' message: 'Please contact your administrator. ID not in valid format!' data: null
- code: 'failed' message: 'User not found!' data: null

IF SUCCESS:
```
    {
       {
        "status": "success",
        "data": {
                "list": [
                    {
                        "id": "15"
                    },
                    {
                        "id": "14"
                    },
                    {
                        "id": "13"
                    },
                    {
                        "id": "12"
                    },
                    {
                        "id": "11"
                    },
                    {
                        "id": "10"
                    },
                    {
                        "id": "9"
                    },
                    {
                        "id": "8"
                    },
                    {
                        "id": "7"
                    },
                    {
                        "id": "6"
                    },
                    {
                        "id": "5"
                    },
                    {
                        "id": "4"
                    }
                ],
                "last_id": {
                    "id": "4"
                }
            }
        }
    }
```

#### ADDITIONAL HOME FEED LIST => /datavice/api/v1/feeds/h_feeds
REQUEST:
- Method: GET
- Header: key=Content-Type, value=application/x-www-form-urlencoded
- Params: key=wpid (value=User ID), key=snky (value=Session Key), key=LID (value=Last ID of feed list)

RESPONSE:
- code: 'unknown' message: 'Please contact your administrator. Request unknown!' data: null
- code: 'failed' message: 'Please contact your administrator. ID not in valid format!' data: null
- code: 'failed' message: 'User not found!' data: null

IF SUCCESS:
```
    {
        "status": "success",
        "data": {
            "list": [
                {
                    "id": "5"
                },
                {
                    "id": "4"
                },
                {
                    "id": "3"
                },
                {
                    "id": "2"
                },
                {
                    "id": "1"
                }
            ],
            "last_id": {
                "id": "1"
            }
        }
    }
```



#### ACTIVITY LOG CREATE => /datavice/api/v1/activity/c_activity
REQUEST:
- Method : POST
- Header : key=Content-Type, value=application/xx-www-form-urlencoded
- Params : Key=wpid (value=User ID), key=snky (value=Session Key), key=stid (value=Store ID), key=icon (value=Activity Log Icon {'info','warn','error'}), key=title (value=Activity Title), key=info (value=Activity information)

RESPONSE:
- code: 'unknown' message: 'Please contact your administrator. Request unknown!' data: null
- code: 'failed' message: 'Please contact your administrator. ID not valid format' data: null
- code: 'failed' message: 'User not found' data: null
- code: 'unknown' message: 'Please contact your administrator. Activity Log Creation Failed' data: null
- code: 'success' message: 'Activity Log Created Successfully' data: null

IF SUCCESS:
```
    {
        "status" => "Success",
	    "message" => "Activity Log created Successfuly",
    }
```

#### ACTIVITY LOG FETCH => /datavice/api/v1/activity/get_act_feed
REQUEST:
- Method : GET
- Header : key=Content-Type, value=application/xx-www-form-urlencoded
- Params : Key=wpid (value=User ID), key=snky (value=Session Key)

RESPONSE:
- code: 'unknown' message: 'Please contact your administrator. Request unknown!' data: null
- code: 'failed' message: 'Please contact your administrator. ID not valid format' data: null
- code: 'failed' message: 'User not found' data: null
- code: 'unknown' message: 'Please contact your administrator. Activity Log Creation Failed' data: null
- code: 'success' message: 'Activity Log Created Successfully' data: null

IF SUCCESS:
```
    {
        {
            "status": "success",
            "data": {
                "list": [
                    {
                        "parent_id": "12",
                        "date_created": "2020-07-24 11:01:15",
                        "title": "some title",
                        "info": "some info"
                    },
                    {
                        "parent_id": "11",
                        "date_created": "2020-07-24 10:58:32",
                        "title": "some title",
                        "info": "some info"
                    },
                    {
                        "parent_id": "10",
                        "date_created": "2020-07-24 10:55:46",
                        "title": "some title",
                        "info": ""
                    },
                    {
                        "parent_id": "4",
                        "date_created": "2020-07-24 08:46:50",
                        "title": "You added new Friend! #4.1",
                        "info": "The red brown fox jumped over the lazy dog.4"
                    },
                    {
                        "parent_id": "3",
                        "date_created": "2020-07-24 08:36:41",
                        "title": "You added new Friend! #3",
                        "info": "The red brown fox jumped over the lazy dog."
                    },
                    {
                        "parent_id": "2",
                        "date_created": "2020-07-24 08:36:21",
                        "title": "You added new Friend! #2",
                        "info": "The red brown fox jumped over the lazy dog."
                    }
                ],
                "last_id": {
                    "parent_id": "2",
                    "date_created": "2020-07-24 08:36:21",
                    "title": "You added new Friend! #2",
                    "info": "The red brown fox jumped over the lazy dog."
                }
            }
        }
    }
```


#### ACTIVITY LOG FETCH ADDITIONAL FEED => /datavice/api/v1/activity/get_act_addfeed
REQUEST:
- Method : GET
- Header : key=Content-Type, value=application/xx-www-form-urlencoded
- Params : Key=wpid (value=User ID), key=snky (value=Session Key),  key=lid (value=last id of activity)

RESPONSE:
- code: 'unknown' message: 'Please contact your administrator. Request unknown!' data: null
- code: 'failed' message: 'Please contact your administrator. ID not valid format' data: null
- code: 'failed' message: 'User not found' data: null
- code: 'unknown' message: 'Please contact your administrator. Activity Log Creation Failed' data: null
- code: 'success' message: 'success' data: array List, last_id

IF SUCCESS:
```
    {
        {
            "status": "success",
            "data": {
                "list": [
                    {
                        "parent_id": "4",
                        "date_created": "2020-07-24 08:46:50",
                        "title": "You added new Friend! #4.1",
                        "info": "The red brown fox jumped over the lazy dog.4"
                    },
                    {
                        "parent_id": "3",
                        "date_created": "2020-07-24 08:36:41",
                        "title": "You added new Friend! #3",
                        "info": "The red brown fox jumped over the lazy dog."
                    },
                    {
                        "parent_id": "2",
                        "date_created": "2020-07-24 08:36:21",
                        "title": "You added new Friend! #2",
                        "info": "The red brown fox jumped over the lazy dog."
                    }
                ],
                "last_id": {
                    "parent_id": "2",
                    "date_created": "2020-07-24 08:36:21",
                    "title": "You added new Friend! #2",
                    "info": "The red brown fox jumped over the lazy dog."
                }
            }
        }
    }
```

#### ACTIVITY LOG FETCH ACTIVTY BY ID => /datavice/api/v1/activity/get_act_byid
REQUEST:
- Method : GET
- Header : key=Content-Type, value=application/xx-www-form-urlencoded
- Params : Key=wpid (value=User ID), key=snky (value=Session Key),  key=atid (value=Activty ID)

RESPONSE:
- code: 'unknown' message: 'Please contact your administrator. Request unknown!' data: null
- code: 'failed' message: 'Please contact your administrator. ID not valid format' data: null
- code: 'failed' message: 'Parammeters not in valid format!' data: null
- code: 'failed' message: 'User not found' data: null
- code: 'unknown' message: 'Please contact your administrator. Activity Unknown' data: null
- code: 'unknown' message: 'Please contact your administrator. Request not Found' data: null
- code: 'success' message: 'success' data: array List
- code: 'failed' message: 'please contact your admininistrator. Theres no activity on your log with this ID' data: null
IF SUCCESS:
```
    {
        {
            "status": "success",
            "data": {
                "list": {
                    "id": "4",
                    "title": "You added new Friend! #4.1",
                    "info": "The red brown fox jumped over the lazy dog.4",
                    "date_created": "The red brown fox jumped over the lazy dog.4"
                }
            }
        }
    }
```





## Contributing

Please read [CONTRIBUTING](CONTRIBUTING) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/BytesCrafter/SocioPress-WP-Plugin/tags). 

## Authors

* **Bytes Crafter** - *Initial work* - [Github](https://github.com/BytesCrafter/SocioPress-WP-Plugin.git)

See also the list of [contributors](https://github.com/BytesCrafter/SocioPress-WP-Plugin/graphs/contributors) who participated in this project.

## License

This project is licensed under the GNU GPL License - see the [LICENSE](LICENSE) file for details

## Acknowledgments

* WordPress.Org
* BitCoin.Org