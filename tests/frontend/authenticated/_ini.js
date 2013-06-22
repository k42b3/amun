
testCase('login', {

	enterCredentials: function(){
		Assert.object(window.amun.user);
		Assert.equals(2, window.amun.user.id);
		Assert.equals('Anonymous', window.amun.user.name);

		document.getElementById('identity').value = 'test@test.com';
		document.getElementById('pw').value = 'test123';
		document.getElementsByTagName('form')[0].submit();
	}

});

