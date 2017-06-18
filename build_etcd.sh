#!/usr/bin/env bash

wget -c https://github.com/coreos/etcd/releases/download/v3.2.0/etcd-v3.2.0-linux-amd64.tar.gz
tar xzf etcd-v3.2.0-linux-amd64.tar.gz
cd etcd-v3.2.0-linux-amd64

./etcd &> /dev/null &