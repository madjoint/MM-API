require 'rubygems'
require 'json'
require 'redis'

$redis = Redis.connect(:db => '2')

buffer = '';
while line = gets
  line.strip!
  line.sub!('+','')
  date = line.match(/\[.*\]/).to_s.sub('[','').split(' ')[0].strip
  phone = line.match(/ [+]*[0-9]+ -/).to_s.strip.sub(' -','')
  cmd = line.match(/ - [Ll][0-9]+/).to_s.sub(' - ','').strip
  print phone + " " + date + " " + cmd + "\n"
  $redis.sadd("mobile_number:#{phone}:visit_dates", date)
  $redis.sadd("mobile_numbers:visit_dates", phone)
  if cmd != ''
    $redis.sadd("mobile_numbers:used_list_cmd", phone)
  end
end
