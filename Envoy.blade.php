@servers(['docker' => ['stamm-silva@zoomyboy.de', 'stammgallier@stamm-gallier.de', 'dpsg-lennep@zoomyboy.de', 'dpsgbergischland@zoomyboy.de', 'dpsg@adrema.dpsg-koeln.de']])

@task('deploy', ['on' => 'docker'])
cd $ADREMA_PATH
docker compose down
docker compose pull
docker compose up -d
@endtask
