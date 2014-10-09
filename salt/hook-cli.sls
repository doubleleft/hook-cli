{% set user = salt['pillar.get']('project_username','vagrant') %}
{% set www_root = salt['pillar.get']('project_path','/vagrant') %}
{% if grains['host'] in ['ddll','staging'] %}
  {% set serv_domain = 'http://hook.ddll.co' %}
{% else %}
  {% set serv_domain = salt['pillar.get']('serv_domain','localhost') %}
{% endif %}

hook-cli:
  pkg.installed:
    - pkgs:
      - make
      - php5-cli
      - php5-curl
      - npm
      - nodejs-legacy

  cmd.run:
    - name: make
    - cwd: {{ www_root }}
    - user: {{ user }}
    - require:
      - pkg: hook-cli

