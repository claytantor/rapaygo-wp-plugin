Folders in your plugin might include:

css or styles for stylesheets
scripts for JavaScript
includes for include files
templates for template files that your plugin outputs
assets for media and other asset files
i18n for internationalisation files


## remove volumes (freshie)
```
clay@orion-lap:~/data/github.com/claytantor/rapaygo-wp-plugin$ docker-compose down --volumes
Stopping rapaygo-wp-plugin_wordpress_1 ... done
Stopping rapaygo-wp-plugin_db_1        ... done
Removing rapaygo-wp-plugin_wordpress_1 ... done
Removing rapaygo-wp-plugin_db_1        ... done
Removing network rapaygo-wp-plugin_default
Removing volume rapaygo-wp-plugin_db_data
Removing volume rapaygo-wp-plugin_wordpress_data
```

## making the docker compose
```
mkdir db_data
docker volume create --driver local \
    --opt type=none \
    --opt device=$(pwd)/db_data \
    --opt o=bind db_data

mkdir wordpress_data
docker volume create --driver local \
    --opt type=none \
    --opt device=$(pwd)/wordpress_data \
    --opt o=bind wordpress_data
```

## up docker 
```
clay@orion-lap:~/data/github.com/claytantor/rapaygo-wp-plugin$ docker-compose up -d
Creating network "rapaygo-wp-plugin_default" with the default driver
Creating volume "rapaygo-wp-plugin_db_data" with default driver
Creating volume "rapaygo-wp-plugin_wordpress_data" with default driver
Creating rapaygo-wp-plugin_db_1 ... done
Creating rapaygo-wp-plugin_wordpress_1 ... done
clay@orion-lap:~/data/github.com/claytantor/rapaygo-wp-plugin$ 
```

## manage wp
http://localhost:8000/

## deploy the plugin
`sudo bash ./scripts/deploy-local.sh`