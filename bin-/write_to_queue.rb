require 'rubygems'
require 'json'
require 'redis'

$redis = Redis.connect(:db => '2')

class LeftronicQueue
  def method_missing(method, *args, &block)
    $redis.rpush(method.to_s, args.to_json)
    # print "method:" + method.to_s + "args:" + print args.to_json + "\n"
  end
end

lq = LeftronicQueue.new
lq.pushNumber("user_count_single", 122)
lq.pushText("interests","Title2","Interest2")
