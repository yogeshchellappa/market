import urllib.parse
import urllib.request
import json

f = open("id.txt")
id =  f.read().strip()     
f.close()
id = str(id)

url ="http://itunes.apple.com/rss/customerreviews/id=" + id + '/json'
	
response = urllib.request.urlopen(url.format(id))
dict_count = {"pos" : 0, "neg" : 0, "neutral" : 0}	
data = json.loads(response.read().decode("utf-8"))
emotion = ""

for i in range(1, len(data["feed"]["entry"])):
	if(data["feed"]["entry"][i]["content"]["label"]):
		res = data["feed"]["entry"][i]["content"]["label"]
	else:
		res = data["feed"]["entry"][i]["title"]["label"]
	params = urllib.parse.urlencode({'text':res})
	params = params.encode("utf-8")
	results = urllib.request.urlopen("http://text-processing.com/api/sentiment/", params)
	r = json.loads(results.read().decode("utf-8"))
	sentiment = r['label']
	dict_count[sentiment] += 1
	if((dict_count["pos"] >= 3) or (dict_count["neg"] >= 3)):
		emotion = sentiment
		break

# In case there are less than 5 reviews, then emotion will be null still		
if(emotion == ""):
		emotion = max(dict_count, key = dict_count.get)
print(emotion)
	
	