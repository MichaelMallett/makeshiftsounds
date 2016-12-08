vm:
	rm -f drupalvm/config.yml
	ln -s ../config/config.yml drupalvm/config.yml
site-symlinks:
	ln -s ../../../../code/modules docroot/sites/all/modules/custom
	ln -s ../../../../code/themes docroot/sites/all/themes/custom
	ln -s ../../../../code/features docroot/sites/all/modules/features
	ln -s ../../../code/drush docroot/sites/all/drush
	ln -sf ../../../config/misc.yml drupalvm/provisioning/tasks/misc.yml
	ln -sf ../../../code/settings.php docroot/sites/default/settings.php
shared-symlinks:
	sudo rm -rf docroot/sites/default/files
	ln -s ../../../shared/files docroot/sites/default/files
	chmod -R 775 shared/files
site:
	sudo rm -rf docroot
	drush make drupal.make.yml docroot
	make vm
	make site-symlinks
	mkdir -p shared
	mkdir -p shared/files
	make shared-symlinks
site-clean:
	cd drupalvm && vagrant halt
	sudo rm -rf docroot
	sudo rm -rf shared
site-install:
	make site
	cd drupalvm && sudo ansible-galaxy install -r provisioning/requirements.yml --force
	cd drupalvm && vagrant up --provision
site-core-update:
	sudo rm -rf docroot
	drush make drupal.make.yml docroot
	make shared-symlinks
	echo "Please restart your engines (Vagrant reload)"
pan-db-copy:
	sudo chmod -f 775 docroot/sites/default
	make site
	cd docroot && drush sql-drop -y
	terminus sites aliases
	cd docroot && drush sql-sync @pantheon.rawpixel-full.test @rawpixel.local.rawpixel.com --structure-tables-key=common -y
	redis-cli FLUSHALL
	drush dl composer -y && drush cc drush
	cd docroot && drush composer-json-rebuild
	cd docroot && drush @rawpixel.local.rawpixel.com composer-manager update
	cd docroot && ../deploy/common_deploy_tasks.sh @rawpixel.local.rawpixel.com dev
	cd docroot && drush @rawpixel.local.rawpixel.com uli
pan-db-create:
	terminus site backups create --site=rawpixel-full --env=dev --element=database
pan-files-copy:
	terminus site backups get --element=files --site=rawpixel-full --env=dev --to=shared/files/files.backup.latest.tar.gz --latest
	tar xvfz shared/files/files.backup.latest.tar.gz --directory shared/files/ --strip-components=1
	rm shared/files/files.backup.latest.tar.gz
pan-files-create:
	terminus site backups create --site=rawpixel-full --env=dev --element=files
build-circle:
	rm -rf docroot
	mysqladmin create drupal
	drush make drupal.make.yml docroot
	mv code/modules docroot/sites/all/modules/custom
	mv code/themes docroot/sites/all/themes/custom
	mv code/drush docroot/sites/all/drush
	mv code/features docroot/sites/all/modules/features
	mv code/settings.php docroot/sites/default/settings.php
	mv config/schema.xml docroot/sites/all/modules/contrib/search_api_solr/solr-conf/3.x/schema.xml
	cd docroot && drush site-install -y --site-name=Rawpixel --account-name=admin --account-pass=admin
	cd docroot && drush en master -y
	cd docroot && drush master-execute --scope=dev -y
	drush dl composer-8.x-1.x && drush cc drush
	cd docroot && drush composer-manager install
	cd docroot && drush composer-json-rebuild
pan-deploy-staging:
	terminus sites aliases
	terminus site deploy --site=rawpixel-full --env=test
	cd docroot && ../deploy/common_deploy_tasks.sh @pantheon.rawpixel-full.test pantheon
pan-deploy-prod:
	terminus sites aliases
	terminus site deploy --site=rawpixel-full --env=prod
	cd docroot && ../deploy/common_deploy_tasks.sh @pantheon.rawpixel-full.prod pantheon
