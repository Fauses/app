local libraryUtil = {}

function libraryUtil.checkType( name, argIdx, arg, expectType, nilOk )
	if arg == nil and nilOk then
		return
	end
	if type( arg ) ~= expectType then
		local msg = string.format( "bad argument #%d to '%s' (%s expected, got %s)",
			argIdx, name, expectType, type( arg )
		)
		error( msg, 3 )
	end
end

function libraryUtil.checkTypeForIndex( index, value, expectType )
	if type( value ) ~= expectType then
		local msg = string.format( "value for index '%s' must be %s, %s given",
			index, expectType, type( value )
		)
		error( msg, 3 )
	end
end

function libraryUtil.checkTypeForNamedArg( name, argName, arg, expectType, nilOk )
	if arg == nil and nilOk then
		return
	end
	if type( arg ) ~= expectType then
		local msg = string.format( "bad named argument %s to '%s' (%s expected, got %s)",
			argName, name, expectType, type( arg )
		)
		error( msg, 3 )
	end
end

function libraryUtil.makeCheckSelfFunction( libraryName, varName, selfObj, selfObjDesc )
	return function ( self, method )
		if self ~= selfObj then
			error( string.format(
				"%s: invalid %s. Did you call %s with a dot instead of a colon, i.e. " ..
				"%s.%s() instead of %s:%s()?",
				libraryName, selfObjDesc, method, varName, method, varName, method
			), 3 )
		end
	end
end

return libraryUtil
