#include <stdio.h>

int main () {

    int a, b, temp;
    puts("Diga um valor para A:");
    scanf("%d", &a);
    puts("Diga um valor para B:");
    scanf("%d", &b);

    temp = a;
    a = b;
    b = temp;
    printf("Trocando os valores, A agora é %d e B agora é %d.\n", a, b);

    return 0;
}