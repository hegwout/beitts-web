

push to google container registery

docker build -t mytts .
docker tag mytts us.gcr.io/pro-icon-253402/mytts
docker push us.gcr.io/pro-icon-253402/mytts


### 本地测试
curl -H "Content-Type:application/json"     -H "Data_Type:msg" -X POST --data '{"text": "Hello world"}' http://127.0.0.1:8000/tts