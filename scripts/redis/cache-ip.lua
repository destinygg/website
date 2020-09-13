local key, value, maxlength = KEYS[1], ARGV[1], 3

local count = redis.call("ZCOUNT", key, 1, maxlength)
local existingscore = redis.call("ZSCORE", key, value)
if existingscore then
	-- renumber all the elements and make this one the last
	local elements = redis.call("ZRANGEBYSCORE", key, 1, maxlength)
	local i = 1
	for _, v in ipairs(elements) do
		if v == value then
			redis.call("ZADD", key, count, v)
		else
			redis.call("ZADD", key, i, v)
			i = i + 1
		end
	end
	return
end

if count == maxlength then
	-- delete the first element, modify the other elements score down
	-- and add the new one to the end
	redis.call("ZREMRANGEBYSCORE", key, 1, 1)
	local elements = redis.call("ZRANGEBYSCORE", key, 2, maxlength)
	local i = 1
	for _, v in ipairs(elements) do
		redis.call("ZADD", key, i, v)
		i = i + 1
	end
	return redis.call("ZADD", key, count, value)
else
	-- otherwise just insert it with the next score
	return redis.call("ZADD", key, count + 1, value)
end