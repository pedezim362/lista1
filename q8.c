# include <stdio.h>

int main () {

    int a;
    puts("Digite um numero inteiro:");
    scanf("%d", &a);

    printf("O antecessor e o sucessor de %d sao, respectivamente: %d e %d.\n", a, a-1, a+1);

    return 0;
}