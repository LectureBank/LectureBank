// Password strength meter
// This jQuery plugin is written by firas kassem [2007.04.05]
// Firas Kassem  phiras.wordpress.com || phiras at gmail {dot} com
// for more information : http://phiras.wordpress.com/2007/04/08/password-strength-meter-a-jquery-plugin/

var shortPass = '<span class="error"><img src="/images/error.png" align="absmiddle">&nbsp;Must be at least <strong>6</strong> characters.</span>'
var badPass = '<span style="color:orange;"><img src="/images/tick.gif" align="absmiddle">&nbsp;Weak password.</span>'
var goodPass = '<span style="color:yellowgreen;"><img src="/images/tick.gif" align="absmiddle">&nbsp;Good password.</span>'
var strongPass = '<span style="color:green;"><img src="/images/tick.gif" align="absmiddle">&nbsp;Strong password.</span>'



function passwordStrength(pwd,user)
{
    score = 0 
    
    //password < 6
    if (pwd.length < 6 ) { return shortPass }

    //password == user
    if (pwd.toLowerCase()==user.toLowerCase()) return badPass
    
    //password length
    score += pwd.length * 4
    score += ( checkRepetition(1,pwd).length - pwd.length ) * 1
    score += ( checkRepetition(2,pwd).length - pwd.length ) * 1
    score += ( checkRepetition(3,pwd).length - pwd.length ) * 1
    score += ( checkRepetition(4,pwd).length - pwd.length ) * 1

    //password has 3 numbers
    if (pwd.match(/(.*[0-9].*[0-9].*[0-9])/))  score += 5 
    
    //password has 2 sybols
    if (pwd.match(/(.*[!,@,#,$,%,^,&,*,?,_,~].*[!,@,#,$,%,^,&,*,?,_,~])/)) score += 5 
    
    //password has Upper and Lower chars
    if (pwd.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/))  score += 10 
    
    //password has number and chars
    if (pwd.match(/([a-zA-Z])/) && pwd.match(/([0-9])/))  score += 15 
    //
    //password has number and symbol
    if (pwd.match(/([!,@,#,$,%,^,&,*,?,_,~])/) && pwd.match(/([0-9])/))  score += 15 
    
    //password has char and symbol
    if (pwd.match(/([!,@,#,$,%,^,&,*,?,_,~])/) && pwd.match(/([a-zA-Z])/))  score += 15 
    
    //password is just a nubers or chars
    if (pwd.match(/^\w+$/) || pwd.match(/^\d+$/) )  score -= 10 
    
    //verifing 0 < score < 100
    if ( score < 0 )  score = 0 
    if ( score > 100 )  score = 100 
    
    if (score < 34 )  return badPass 
    if (score < 68 )  return goodPass
    return strongPass
}


// checkRepetition(1,'aaaaaaabcbc')   = 'abcbc'
// checkRepetition(2,'aaaaaaabcbc')   = 'aabc'
// checkRepetition(2,'aaaaaaabcdbcd') = 'aabcd'

function checkRepetition(pLen,str) {
    res = ""
    for ( i=0; i<str.length ; i++ ) {
        repeated=true
        for (j=0;j < pLen && (j+i+pLen) < str.length;j++)
            repeated=repeated && (str.charAt(j+i)==str.charAt(j+i+pLen))
        if (j<pLen) repeated=false
        if (repeated) {
            i+=pLen-1
            repeated=false
        }
        else {
            res+=str.charAt(i)
        }
    }
    return res
}