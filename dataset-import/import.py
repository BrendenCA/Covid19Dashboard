import mysql.connector, csv
import pycountry
from datetime import datetime

mydb = mysql.connector.connect(
  host="127.0.0.1",
  port=33060,
  user="user",
  password="pw",
  database="db"
)

mycursor = mydb.cursor()

query = "INSERT INTO country (name, code) values(%s,%s)"
country = {} 
with open('dataset.csv', 'r') as csvfile:
    csvf = csv.reader(csvfile)
    headers = next(csvf)
    for x in csvf:
        if x[0]=='':    continue
        print(x[0])
        try:
            mycursor.execute(query, (x[0],pycountry.countries.search_fuzzy(x[0].split(",")[0])[0].alpha_2.lower()))
        except:
            continue
        country[x[0]] = mycursor.lastrowid
mydb.commit()
#print(country)

query = "INSERT INTO cases (created_at, country_id, count) values(%s,%s,%s)"

with open('dataset.csv', 'r') as csvfile:
    csvf = csv.reader(csvfile)
    headers = next(csvf)
    for x in csvf:
        if x[0]=='':    continue
        if x[0] not in country: continue
        for y in range(1, len(x)):
            mycursor.execute(query, (datetime.strptime('0'+headers[y]+'20', "%m%d%y").strftime('%Y-%m-%d %H:%M:%S'), str(country[x[0]]), x[y]))

mydb.commit()
      