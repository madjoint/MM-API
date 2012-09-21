require 'rubygems'
require 'json'
require 'leftronic'
require 'redis'
 
begin
$redis = Redis.connect(:db => '2')
rescue
end

lt = Leftronic.new("TdoAB7Q9b4CaPUMbigsr")

while TRUE
  begin
  
  args = $redis.rpop("pushNumber")
  if args != nil
    Leftronic.pushNumber(*JSON.parse(args))
    p JSON.parse(args).inspect
  end
  
  args = $redis.rpop("pushText")
  if args != nil
    Leftronic.pushText(*JSON.parse(args))
    p JSON.parse(args).inspect
  end

  rescue
  else
  ensure
  end
  
  sleep 1 
end
