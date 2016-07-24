import urllib.parse
import urllib.request
import json

def senti(start_count):
	f = open("package.txt")
	packageName = f.read().strip()
	f.close()
	apiKey = 'c9c9ef3e738e5f56ca2b2ebd6c1992c3'  
		
	url = 'http://api.playstoreapi.com/v1.1/apps/{0}?key={1}'
		
	response = urllib.request.urlopen(url.format(packageName, apiKey))
	
	data = json.loads(response.read().decode("utf-8"));
	
	count = start_count
	dict_count = {"pos":0,"neg":0,"neutral":0}
	for i in data['topReviews'][start_count:start_count+5] :
		review =  i['reviewText']
		review = review.encode('utf-8')
		params = urllib.parse.urlencode({'text':review})
		params = params.encode('utf-8')
		result = urllib.request.urlopen("http://text-processing.com/api/sentiment/", params)
		r = json.loads(result.read().decode("utf-8"))
		sentiment = r["label"]
		dict_count[sentiment]+=1
	
	# if pos == neg, check for next 5 reviews
	if (dict_count["pos"]==dict_count["neg"] or dict_count["pos"]==dict_count["neutral"] or dict_count["neg"]==dict_count["neutral"]):
		return "neutral"
	# if neutral is max, check for next 5 reviews
	if (dict_count["neutral"]>dict_count["pos"] and dict_count["neutral"]>dict_count["neg"]):
		return "neutral"
	else:
		#else, return pos or neg, whichever is more in number
		if dict_count["pos"]>dict_count["neg"]:
			return "pos"
		else:
			return "neg"	

			
start_count = 0 
sentiment = senti(start_count)
while( sentiment == "neutral"):
	# if sentiment is neutral, try again for the next 5 reviews.
	start_count = start_count+5
	sentiment = senti(start_count)
if sentiment=="stop":
	sentiment = "neutral"
print(sentiment)

