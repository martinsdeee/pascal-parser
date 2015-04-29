## Pascal parser (Laravel+Bootstrap 3) 

* Pascal piemērs priekš leksikas anaizatora:

``` pascal
procedure TForm1.Button2Click(Sender: TObject); 
var 
    M:set of char; 
    ch:char; 
    S:string; 
    i,k:integer; 
begin 
    M:=[]; 
    k:=0; 
    for i:=i to ListBox1.Items.Count-1 do 
    begin  
        S:=S+Listbox1.Items[i]; 
        for i:=1 to Length(S) do 
            if S[i] in M then 
                M:=M+[S[i]] 
            else 
                if M>5 then 
                begin 
                    ShowMessage('Impossible') 
                end; 
    end;
end;
```

* Pascal piemērs priekš sintakses analizatora:

``` pascal
for i:=i to ListBox1.Items.Count-1 do 
    S:=S+Listbox1.Items[i]; 
```